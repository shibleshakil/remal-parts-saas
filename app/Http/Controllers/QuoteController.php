<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Sale;
use App\Models\Quote;
use App\Models\Balance;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\SaleDetails;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\QuoteDetails;
use Illuminate\Http\Request;
use App\Models\PurchaseLines;
use App\Models\VehicleDetails;
use Illuminate\Validation\Rule;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Support\Facades\Validator;


class QuoteController extends Controller
{

    private function logError(string $action, \Throwable $e): void
    {
        Log::error("QuoteController {$action} failed: {$e->getMessage()}", [
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Quote::with('items','customer');

        // সার্চ অপশন
        $query->where(function ($q) use ($request) {
            if ($request->filled('search')) {
                $search = $request->search;
                $q->where('quote_no', 'like', "%{$search}%");
            }

           // Date range filter
            if ($request->filled('FromDate') && $request->filled('ToDate')) {
                $from = $request->FromDate;
                $to   = $request->ToDate;
                $q->whereBetween('date', [$from, $to]);
            } elseif ($request->filled('FromDate')) {
                // only from date
                $q->whereDate('date', '>=', $request->FromDate);
            } elseif ($request->filled('ToDate')) {
                // only to date
                $q->whereDate('date', '<=', $request->ToDate);
            }
        });

        // Clone the query before pagination
        $totalFinalAmount = (clone $query)->sum('final_amount');

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $quotes = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();

        $sl = ($quotes->currentPage() - 1) * $perPage;

        return view('quote.index', [
            'sl' => $sl, 'datas' => $quotes,
            'totalFinalAmount' => $totalFinalAmount
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::select('id', 'name', 'phone', 'address')->latest()->get();
        $vehicles = Vehicle::with('details')->select('id', 'name', 'uid')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        $parts = VehicleDetails::whereNull('vehicle_id')
            ->with(['purchaseLines' => function ($q) {
                $q->select('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price')
                    ->groupBy('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price');
            }])
            ->get()
            ->flatMap(function ($item) {
                return $item->purchaseLines->map(function ($line) use ($item) {
                    return (object)[
                        'id' => $line->id,
                        // 'purchase_id' => $line->id,
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float)$line->unit_price,
                        'target_sell_price' => (float)($line->target_sell_price ?? 0),
                        'uid' => $item->uid,
                        'available_quantity' => $item->purchaseLines
                            ->where('unit_price', $line->unit_price)
                            ->sum('quantity'),
                    ];
                });
            });

        return view('quote.create', compact('customers', 'vehicles', 'parts','banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => ['nullable', 'exists:customers,id'],
            'date' => ['required', 'date'],
            'final_amount' => ['required', 'numeric', 'min:0'],
            'products' => ['required', 'array'],
            'products.*.item_id' => ['required'],
            'products.*.item_type' => ['required', Rule::in(['spare_part', 'car_spare_part'])],
            'products.*.item_name' => ['required', 'string', 'max:255'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.cost' => ['required', 'numeric', 'min:0'],
            'products.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'expected_purchase_date' => ['nullable','date']
        ]);

        if ($validator->fails()) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }


        try {
            DB::beginTransaction();

            $quote = Quote::create([
                'customer_id' => $validator->validated()['customer'],
                'date' => $validator->validated()['date'],
                'final_amount' => $validator->validated()['final_amount'],
                'expected_purchase_date' => $validator->validated()['expected_purchase_date'],
                'branch_id' => active_branch_id(),
                'created_by' => auth()->id(),
            ]);

            foreach ($validator->validated()['products'] as $product) {
                $calculatedCost = $product['unit_price'] * $product['quantity'];
                if (round($calculatedCost, 2) !== round($product['cost'], 2)) {
                    throw new \Exception("Invalid cost for {$product['item_name']}. Expected: {$calculatedCost}, Got: {$product['cost']}");
                }

                $purchaseLine = PurchaseLines::find($product['item_id']);

                QuoteDetails::create([
                    'quote_id' => $quote->id,
                    'item_id' => $purchaseLine->vehicle_detail_id ?? $product['item_id'],
                    'purchase_line_id' =>  $product['item_id'],
                    'vehicle_id' => $product['item_type'] === 'car_spare_part' ? ($product['vehicle_id'] ?? null) : null,
                    'item_type' => $product['item_type'],
                    'item_name' => $product['item_name'],
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'cost' => $product['cost'],
                    'branch_id' => active_branch_id(),
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            if (request()->ajax()) {
                    $quote->refresh();
                    $numberToWords = new NumberToWords();
                    // Create number transformer for Arabic
                    $numberTransformer = $numberToWords->getNumberTransformer('ar');
                    $amountInWords =  $numberTransformer->toWords($quote->final_amount);

                    $htmlView = view('quote.invoice', compact('quote','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }
            return redirect()->route('quotes.show', $quote->id)->with('success', 'Quote created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('store', $e);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' =>  $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        $quote = Quote::with(['customer', 'items'])->findOrFail($quote->id);

        $numberToWords = new NumberToWords();

        // Create number transformer for Arabic
        $numberTransformer = $numberToWords->getNumberTransformer('ar');
        $amount = $quote->final_amount;
        $integerPart = floor($amount);
        $decimalPart = round(($amount - $integerPart) * 100);

        $amountInWords = ucfirst($numberTransformer->toWords($integerPart));

        if ($decimalPart > 0) {
            $amountInWords .= 'درهم و ' . $numberTransformer->toWords($decimalPart) . ' فلس';
        }

        return view('quote.invoice', compact('quote','amountInWords'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $quote = Quote::with(['customer', 'quoteDetails'])->findOrFail($id);
        $customers = Customer::select('id', 'name', 'phone', 'address')->latest()->get();
        $vehicles = Vehicle::with('details')->select('id', 'name', 'uid')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        $parts = VehicleDetails::whereNull('vehicle_id')
            ->with(['purchaseLines' => function ($q) {
                $q->select('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price')
                    ->groupBy('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price');
            }])
            ->get()
            ->flatMap(function ($item) {
                return $item->purchaseLines->map(function ($line) use ($item) {
                    return (object)[
                        'id' => $line->id,
                        // 'purchase_id' => $line->id,
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float)$line->unit_price,
                        'target_sell_price' => (float)($line->target_sell_price ?? 0),
                        'uid' => $item->uid,
                        'available_quantity' => $item->purchaseLines
                            ->where('unit_price', $line->unit_price)
                            ->sum('quantity'),
                    ];
                });
            });

        $existingQuantities = $quote->quoteDetails->whereIn('item_type', ['spare_part', 'car_spare_part'])
            ->where('item_id', '!=', '0')
            ->mapWithKeys(function ($item) {
                $part = VehicleDetails::with(['purchaseLines', 'quoteDetails'])->find($item->item_id);
                if (!$part) {
                    return [$item->item_id => 0];
                }
                $availableQuantity = $part->purchaseLines
                    ->where('unit_price', $item->unit_price)
                    ->sum('quantity') - $part->quoteDetails
                    ->where('unit_price', $item->unit_price)
                    ->sum('quantity');
                return [$item->item_id => $availableQuantity + $item->quantity]; // Add back sold quantity
            })->toArray();

        return view('quote.edit', compact('quote', 'customers', 'vehicles', 'parts', 'existingQuantities', 'banks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'customer' => ['nullable', 'exists:customers,id'],
            'date' => ['required', 'date'],
            'final_amount' => ['required', 'numeric', 'min:0'],
            'products' => ['required', 'array'],
            'products.*.item_id' => ['required'],
            'products.*.item_type' => ['required', Rule::in(['spare_part', 'car_spare_part'])],
            'products.*.item_name' => ['required', 'string', 'max:255'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.cost' => ['required', 'numeric', 'min:0'],
            'products.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'expected_purchase_date' => ['nullable','date']
        ]);

        if ($validator->fails()) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $quote = Quote::findOrFail($id);
            $quote->quoteDetails->each(function ($item) {
                if ($item->item_id !== '0') {
                    $part = VehicleDetails::find($item->item_id);
                }
            });

            $quote->update([
                'customer_id' => $validator->validated()['customer'],
                'date' => $validator->validated()['date'],
                'final_amount' => $validator->validated()['final_amount'],
                'expected_purchase_date' => $validator->validated()['expected_purchase_date'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            QuoteDetails::where('quote_id', $quote->id)->delete();

            foreach ($validator->validated()['products'] as $product) {
                $calculatedCost = $product['unit_price'] * $product['quantity'];
                if (round($calculatedCost, 2) !== round($product['cost'], 2)) {
                    throw new \Exception("Invalid cost for {$product['item_name']}. Expected: {$calculatedCost}, Got: {$product['cost']}");
                }

                $purchaseLine = PurchaseLines::find($product['item_id']);

                QuoteDetails::create([
                    'quote_id' => $quote->id,
                    'item_id' => $purchaseLine->vehicle_detail_id ?? $product['item_id'],
                    'purchase_line_id' =>  $product['item_id'],
                    'vehicle_id' => $product['item_type'] === 'car_spare_part' ? ($product['vehicle_id'] ?? null) : null,
                    'item_type' => $product['item_type'],
                    'item_name' => $product['item_name'],
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'cost' => $product['cost'],
                    'updated_by' => auth()->id(),
                ]);
            }

            DB::commit();

            if (request()->ajax()) {
                    $quote->refresh();
                    $numberToWords = new NumberToWords();
                    // Create number transformer for Arabic
                    $numberTransformer = $numberToWords->getNumberTransformer('ar');
                    $amountInWords =  $numberTransformer->toWords($quote->final_amount);

                    $htmlView = view('quote.invoice', compact('quote','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }

            return redirect()->route('quotes.show', $quote->id)->with('success', 'Quote updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('update', $e);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' =>  $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function convertToSale($id)
    {
        $quote = Quote::with(['customer', 'quoteDetails'])->findOrFail($id);
        $customers = Customer::select('id', 'name', 'phone', 'address')->latest()->get();
        $vehicles = Vehicle::with('details')->select('id', 'name', 'uid')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
        $parts = VehicleDetails::whereNull('vehicle_id')
            ->with(['purchaseLines' => function ($q) {
                $q->select('id', 'vehicle_detail_id', 'unit_price', 'quantity')
                    ->groupBy('id', 'vehicle_detail_id', 'unit_price', 'quantity');
            }])
            ->get()
            ->flatMap(function ($item) {
                return $item->purchaseLines->map(function ($line) use ($item) {
                    return (object)[
                        'id' => $line->id,
                        // 'purchase_id' => $line->id,
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float)$line->unit_price,
                        'uid' => $item->uid,
                        'available_quantity' => $item->purchaseLines
                            ->where('unit_price', $line->unit_price)
                            ->sum('quantity'),
                    ];
                });
            });

        $existingQuantities = $quote->quoteDetails->whereIn('item_type', ['spare_part', 'car_spare_part'])
            ->where('item_id', '!=', '0')
            ->mapWithKeys(function ($item) {
                $part = PurchaseLines::find($item->purchase_line_id);
                if (!$part) {
                    return [$item->purchase_line_id => 0];
                }
                $availableQuantity = $part->quantity;

                return [$item->purchase_line_id => $availableQuantity]; // Add back sold quantity
            })->toArray();


        return view('quote.convert_to_sale', compact('quote', 'customers', 'vehicles', 'parts', 'banks','existingQuantities'));
    }

    public function storeToSale(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer' => ['nullable', 'exists:customers,id'],
            'date' => ['required', 'date'],
            'payment_method' => ['required_with:paid_amount|string', Rule::in(['cash', 'bank_transfer', 'cheque','due'])],
            'total_cost' => ['required', 'numeric', 'min:0'],
            // 'discount_amount' => ['nullable', 'numeric', 'min:0'],
            // 'discount_type' => ['nullable', Rule::in(['flat', 'percentage'])],
            'final_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'products' => ['required', 'array'],
            'products.*.item_id' => ['required'],
            'products.*.item_type' => ['required', Rule::in(['spare_part', 'car_spare_part'])],
            'products.*.item_name' => ['required', 'string', 'max:255'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.cost' => ['required', 'numeric', 'min:0'],
            'products.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'promise_payment_date' => ['nullable', 'date'],
            'bank_id' => ['nullable'],
            'products.*.item_actual_price' => ['nullable','numeric','min:0'],
            'quote_id' => ['required', 'exists:quotes,id'],
            // 'send_email' => ['nullable','boolean'],
        ]);

        if ($validator->fails()) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }


        try {
            DB::beginTransaction();

            $itemPurchaseAmount = 0;
            foreach ($validator->validated()['products'] as $product) {
                $itemPurchaseAmount += $product['item_actual_price'] * $product['quantity'];
            }

            $sale = Sale::create([
                'customer_id' => $validator->validated()['customer'],
                'date' => $validator->validated()['date'],
                'payment_method' => $validator->validated()['payment_method'],
                'bank_id' => $validator->validated()['bank_id'],
                'total_cost' => $validator->validated()['total_cost'],
                // 'discount_amount' => $validator->validated()['discount_amount'] ?? 0,
                // 'discount_type' => $validator->validated()['discount_type'],
                'final_amount' => $validator->validated()['final_amount'],
                'paid_amount' => $validator->validated()['paid_amount'],
                'warranty' => $validator->validated()['warranty'],
                'promise_payment_date' => $validator->validated()['promise_payment_date'],
                'created_by' => auth()->id(),
                'item_purchase_amount' => $itemPurchaseAmount,
            ]);

            foreach ($validator->validated()['products'] as $product) {
                $calculatedCost = $product['unit_price'] * $product['quantity'];
                if (round($calculatedCost, 2) !== round($product['cost'], 2)) {
                    throw new \Exception("Invalid cost for {$product['item_name']}. Expected: {$calculatedCost}, Got: {$product['cost']}");
                }

                $purchaseLine = PurchaseLines::find($product['item_id']);

                if ($product['item_id'] !== '0') {
                    $availableQuantity = $purchaseLine->quantity ?? 0;

                    if ($availableQuantity < $product['quantity']) {
                        throw new \Exception("Insufficient stock for {$product['item_name']}. Available: {$availableQuantity}");
                    }

                    $purchaseLine->sold_quantity += $product['quantity'];
                    $purchaseLine->quantity -= $product['quantity'];
                    $purchaseLine->save();
                }

                SaleDetails::create([
                    'sale_id' => $sale->id,
                    'item_id' => $purchaseLine->vehicle_detail_id ?? $product['item_id'],
                    'purchase_line_id' =>  $product['item_id'],
                    'item_actual_price' => $purchaseLine->unit_price ?? $product['unit_price'],
                    'vehicle_id' => $product['item_type'] === 'car_spare_part' ? ($product['vehicle_id'] ?? null) : null,
                    'item_type' => $product['item_type'],
                    'item_name' => $product['item_name'],
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'cost' => $product['cost'],
                    'created_by' => auth()->id(),
                ]);
            }

            // update balance
            $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );
            $balance->increment('balance', $validator->validated()['paid_amount'] ?? 0);
            $balance->refresh();

            Transaction::create([
                'trxid' => 'TRX-' . Str::uuid(),
                'type' => 'sale',
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'transaction_date' => $validator->validated()['date'],
                'final_amount' => $validator->validated()['final_amount'],
                'status'           => $validator->validated()['paid_amount'] == 0 ? 'unpaid' : ($validator->validated()['final_amount'] > $validator->validated()['paid_amount'] ? 'partially_paid' : 'paid'),
                'note' => 'Sale transaction for ' . ($validator->validated()['customer'] ? 'customer ID: ' . $validator->validated()['customer'] : 'Walking Customer'),
                'created_by' => auth()->id(),
                'payment_method'   => $validator->validated()['payment_method'],
                'bank_id'          => $validator->validated()['bank_id'],
                'paid_amount'      => $validator->validated()['paid_amount'],
                'current_balance'  => $balance->balance
            ]);

            $quote = Quote::find($validator->validated()['quote_id']);

            $quote->update([
                'status' => 'converted-to-sale',
                'sale_id' => $sale->id,
                'converted_at' => now(),
                'converted_by' => auth()->id(),
            ]);

            // $customer_email = Customer::where('id',$validator->validated()['customer'])->first();
            // if($request->send_email && $customer_email && $customer_email->email){
            //     try {

            //         Mail::to($customer_email->email)->send(new SaleMail());
            //         Log:info("Sale email sent to " . $customer_email->email);
            //     } catch (\Exception $e) {
            //         Log::error("Failed to send sale email: " . $e->getMessage());
            //     }

            // }

            DB::commit();
            if (request()->ajax()) {
                    $numberToWords = new NumberToWords();
                    // Create number transformer for Arabic
                    $numberTransformer = $numberToWords->getNumberTransformer('ar');
                    $amountInWords =  $numberTransformer->toWords($sale->final_amount);

                    $htmlView = view('sale.invoice', compact('sale','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }
            return redirect()->route('sales.show', $sale->id)->with('success', 'Sale created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('store', $e);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' =>  $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        try {
            DB::beginTransaction();

            $quote = Quote::with('items')->findOrFail($id);
            QuoteDetails::where('quote_id', $quote->id)->delete();

            $quote->update(['deleted_by' => auth()->id()]);
            $quote->delete();

            DB::commit();
            return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Quote deleted successfully!'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->logError('delete', $e);
            return response()->json([
                'type' => 'error',
                'title' => __('Error'),
                'msg' => __('An error occurred while deleting the quote.'),
            ], 500);
        }
    }
}
