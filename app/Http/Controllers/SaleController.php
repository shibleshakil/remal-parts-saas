<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Sale;
use App\Mail\SaleMail;
use App\Models\Balance;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\SaleDetails;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PurchaseLines;
use App\Models\VehicleDetails;
use App\Models\PartsReturnList;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    private function logError(string $action, \Throwable $e): void
    {
        Log::error("SaleController {$action} failed: {$e->getMessage()}", [
            'trace' => $e->getTraceAsString(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Sale::with('items','customer');

        // সার্চ অপশন
        $query->where(function ($q) use ($request) {
            if ($request->filled('search')) {
                $search = $request->search;
                $q->where('invoice_no', '=', "$search");
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
        $totalPaidAmount = (clone $query)->sum('paid_amount');

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $sales = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();

        $sl = ($sales->currentPage() - 1) * $perPage;

        return view('sale.index', [
            'sl' => $sl, 'datas' => $sales,
            'totalFinalAmount' => $totalFinalAmount,
            'totalPaidAmount' => $totalPaidAmount,
        ]);
    }

    public function show($id)
    {
        $sale = Sale::with(['customer', 'items','transactions'])->findOrFail($id);

        $numberToWords = new NumberToWords();

        // Create number transformer for Arabic
        $numberTransformer = $numberToWords->getNumberTransformer('ar');
        $amount = $sale->final_amount;
        $integerPart = floor($amount);
        $decimalPart = round(($amount - $integerPart) * 100);

        $amountInWords = ucfirst($numberTransformer->toWords($integerPart));

        if ($decimalPart > 0) {
            $amountInWords .= 'درهم و ' . $numberTransformer->toWords($decimalPart) . ' فلس';
        }

        return view('sale.invoice', compact('sale','amountInWords'));
    }

    public function create()
    {
        $customers = Customer::select('id', 'name', 'phone', 'address')->latest()->get();
        $vehicles = Vehicle::with('details.purchaseLines')->select('id', 'name', 'uid')->get();
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
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float) $line->unit_price,
                        'target_sell_price' => $line->target_sell_price,
                        'uid' => $item->uid,
                        'available_quantity' => $item->purchaseLines
                            ->where('unit_price', $line->unit_price)
                            ->sum('quantity'),
                    ];
                });
            })
            ->filter(function ($item) {
                return $item->available_quantity > 0;
            })
            ->values();

        return view('sale.create', compact('customers', 'vehicles', 'parts','banks'));
    }

    public function customeStoreViaAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s]+$/u'],
            'phone' => ['nullable', 'string', 'max:100', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customerData = $validator->validated();
            $customerData['branch_id'] = active_branch_id();
            $customer = Customer::create($customerData);

            return response()->json([
                'status' => 'success',
                'message' => 'Customer added successfully',
                'customer' => $customer
            ], 201);
        } catch (\Exception $e) {
            $this->logError('customeStoreViaAjax', $e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer',
            ], 500);
        }
    }

    public function bankStoreViaAjax(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255|unique:banks,account_no',
            'branch' => 'nullable|string|max:255',
        ]);



        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $bankData = $validator->validated();
            $bankData['branch_id'] = active_branch_id();
            $bank =Bank::create($bankData);

            return response()->json([
                'status' => 'success',
                'message' => 'Bank added successfully',
                'bank' => $bank
            ], 201);
        } catch (\Exception $e) {
            $this->logError('bankStoreViaAjax', $e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create bank',
            ], 500);
        }
    }

    public function getSpareParts($vehicleId)
    {
        try {
            $parts = VehicleDetails::where('vehicle_id', $vehicleId)
                ->with(['purchaseLines' => function ($q) {
                    $q->select('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price')
                        ->groupBy('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price');
                }])
                ->get()
                ->flatMap(function ($item) {
                    return $item->purchaseLines->map(function ($line) use ($item) {
                        return [
                            'id' => $line->id,
                            'parts_name' => $item->parts_name,
                            'car_name' => $item->car_name,
                            'car_model' => $item->car_model,
                            'uid' => $item->uid,
                            'unit_price' => (float)$line->unit_price,
                            'target_sell_price' => $line->target_sell_price,
                            'available_quantity' => $item->purchaseLines
                                ->where('unit_price', $line->unit_price)
                                ->sum('quantity'),
                        ];
                    });
                })
                ->filter(function ($item) {
                    return $item['available_quantity'] > 0;
                })
                ->values();

            return response()->json($parts);
        } catch (\Exception $e) {
            $this->logError('getSpareParts', $e);
            return response()->json(['error' => 'Failed to fetch spare parts'], 500);
        }
    }

    public function getDataByBarcode($barcode)
    {
        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode is required',
            ], 400);
        }

        try {
            $parts = VehicleDetails::where('uid', $barcode)
                ->orWhereHas('vehicle', function ($query) use ($barcode) {
                    $query->where('uid', $barcode);
                })
                ->with(['purchaseLines' => function ($q) {
                    $q->select('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price')
                        ->groupBy('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price');
                }])
                ->get()
                ->flatMap(function ($item) {
                    return $item->purchaseLines->map(function ($line) use ($item) {
                        return [
                            'id' => $line->id,
                            'vehicle_id' => $item->vehicle_id,
                            'uid' => $item->uid,
                            'parts_name' => $item->parts_name,
                            'car_name' => $item->car_name,
                            'car_model' => $item->car_model,
                            'unit_price' => (float)$line->unit_price,
                            'target_sell_price' => $line->target_sell_price,
                            'available_quantity' => $item->purchaseLines
                                ->where('unit_price', $line->unit_price)
                                ->sum('quantity'),
                        ];
                    });
                })
                ->filter(function ($item) {
                    return $item['available_quantity'] > 0;
                })
                ->values();

            if ($parts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "{$barcode} Parts is out of stock or not found",
                ], 404);
            }

            return response()->json([
                'success' => true,
                'single' => $parts->count() === 1,
                'data' => $parts->count() === 1 ? $parts->first() : $parts,
                'car_id' => $parts->first()['vehicle_id'] ?? null
            ]);
        } catch (\Exception $e) {
            $this->logError('getDataByBarcode', $e);
            return response()->json(['error' => 'Failed to fetch barcode data'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => ['nullable', 'exists:customers,id'],
            'date' => ['required', 'date'],
            'payment_method' => ['required_with:paid_amount|string', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
            'total_cost' => ['required', 'numeric', 'min:1'],
            // 'discount_amount' => ['nullable', 'numeric', 'min:0'],
            // 'discount_type' => ['nullable', Rule::in(['flat', 'percentage'])],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'vat_type' => ['required', 'string'],
            'final_amount' => ['required', 'numeric', 'min:1'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'products' => ['required', 'array'],
            'products.*.item_id' => ['required'],
            'products.*.item_type' => ['required', Rule::in(['spare_part', 'car_spare_part'])],
            'products.*.item_name' => ['required', 'string', 'max:255'],
            'products.*.unit_price' => ['required', 'numeric', 'min:1'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.cost' => ['required', 'numeric', 'min:1'],
            'products.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'promise_payment_date' => ['nullable', 'date'],
            'bank_id' => ['nullable'],
            'products.*.item_actual_price' => ['nullable','numeric'],
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
                'vat_type' => $validator->validated()['vat_type'],
                'vat_amount' => $validator->validated()['vat_amount'],
                'vat' => config('app.vat'),
                'final_amount' => $validator->validated()['final_amount'],
                'paid_amount' => $validator->validated()['paid_amount'],
                'warranty' => $validator->validated()['warranty'],
                'promise_payment_date' => $validator->validated()['promise_payment_date'],
                'branch_id' => active_branch_id(),
                'created_by' => auth()->id(),
                'item_purchase_amount' => $itemPurchaseAmount,
            ]);

            foreach ($validator->validated()['products'] as $product) {
                $calculatedCost = $product['unit_price'] * $product['quantity'];
                if (round($calculatedCost, 2) !== round($product['cost'], 2)) {
                    throw new \Exception("Invalid cost for {$product['item_name']}. Expected: {$calculatedCost}, Got: {$product['cost']}");
                }

                // Branch-scoped lookup with row lock: a cross-branch or missing
                // line id must fail loud instead of recording stock-less sales.
                $purchaseLine = null;
                if ($product['item_id'] !== '0' && $product['item_id'] !== null) {
                    $purchaseLine = PurchaseLines::lockForUpdate()->findOrFail($product['item_id']);
                }

                if ($purchaseLine) {
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
                    'item_actual_price' => $product['item_id'] !== '0' ?  $purchaseLine->unit_price : $product['item_actual_price'],
                    'vehicle_id' => $product['item_type'] === 'car_spare_part' ? ($product['vehicle_id'] ?? null) : null,
                    'item_type' => $product['item_type'],
                    'item_name' => trim(preg_replace('/\s+/', ' ', $product['item_name'])),
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'cost' => $product['cost'],
                    'created_by' => auth()->id(),
                    'branch_id' => active_branch_id(),
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
                'status'           => $validator->validated()['paid_amount'] == 0 ? 'unpaid' : ($validator->validated()['paid_amount'] < $validator->validated()['final_amount'] ? 'partially_paid' : 'paid'),
                'note' => 'Sale transaction for ' . ($validator->validated()['customer'] ? 'customer ID: ' . $validator->validated()['customer'] : 'Walking Customer'),
                'created_by' => auth()->id(),
                'payment_method'   => $validator->validated()['payment_method'],
                'bank_id'          => $validator->validated()['bank_id'],
                'paid_amount'      => $validator->validated()['paid_amount'],
                'current_balance'  => $balance->balance,
                'branch_id' => active_branch_id(),
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
                    $sale->load(['customer', 'items', 'transactions']);

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

    public function edit($id)
    {
        $sale = Sale::with('items', 'items.purchaseLines','transactions')->findOrFail($id);

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
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float) $line->unit_price,
                        'target_sell_price' => $line->target_sell_price,
                        'uid' => $item->uid,
                        'available_quantity' => $item->purchaseLines
                            ->where('unit_price', $line->unit_price)
                            ->sum('quantity'),
                    ];
                });
            })
            ->filter(function ($item) {
                return $item->available_quantity > 0;
            })
            ->values();

        $existingQuantities = $sale->items->whereIn('item_type', ['spare_part', 'car_spare_part'])
            ->where('item_id', '!=', '0')
            ->mapWithKeys(function ($item) {
                $part = PurchaseLines::find($item->purchase_line_id);
                // $part = VehicleDetails::with(['purchaseLines', 'saleDetails'])->find($item->item_id);

                if (!$part) {
                    return [$item->purchase_line_id => 0];
                }
                $availableQuantity = $part->quantity;

                return [$item->purchase_line_id => $availableQuantity + $item->quantity]; // Add back sold quantity
            })->toArray();

        return view('sale.edit', compact('sale', 'customers', 'vehicles', 'parts', 'existingQuantities','banks'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer' => ['nullable', 'exists:customers,id'],
            'date' => ['required', 'date'],
            'payment_method' => ['required_with:paid_amount|string', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
            'total_cost' => ['required', 'numeric', 'min:1'],
            // 'discount_amount' => ['nullable', 'numeric', 'min:0'],
            // 'discount_type' => ['nullable', Rule::in(['flat', 'percentage'])],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'vat_type' => ['required', 'string'],
            'final_amount' => ['required', 'numeric', 'min:1'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'products' => ['required', 'array'],
            'products.*.item_id' => ['required'],
            'products.*.item_type' => ['required', Rule::in(['spare_part', 'car_spare_part'])],
            'products.*.item_name' => ['required', 'string', 'max:255'],
            'products.*.unit_price' => ['required', 'numeric', 'min:1'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.cost' => ['required', 'numeric', 'min:1'],
            'products.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'products.*.item_actual_price' => ['nullable','numeric'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'promise_payment_date' => ['nullable', 'date'],
            'bank_id' => ['nullable'],
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
            $sale = Sale::with('items')->findOrFail($id);


            $oldPaidAmount = $sale->paid_amount ?? 0;

            $sale->update([
                'customer_id' => $validator->validated()['customer'],
                'date' => $validator->validated()['date'],
                'payment_method' => $validator->validated()['payment_method'],
                'bank_id' => $validator->validated()['bank_id'],
                'total_cost' => $validator->validated()['total_cost'],
                // 'discount_amount' => $validator->validated()['discount_amount'] ?? 0,
                // 'discount_type' => $validator->validated()['discount_type'],
                'vat' => $sale->vat,
                'vat_type' => $validator->validated()['vat_type'],
                'vat_amount' => $validator->validated()['vat_amount'],
                'final_amount' => $validator->validated()['final_amount'],
                // 'paid_amount' => $validator->validated()['paid_amount'],
                'warranty' => $validator->validated()['warranty'],
                'promise_payment_date' => $validator->validated()['promise_payment_date'] ?? null,
                'updated_by' => auth()->id(),
            ]);

             // Restore stock before deleting old sale details
            $previousSaleDetails = SaleDetails::where('sale_id', $sale->id)->get();

            foreach ($previousSaleDetails as $oldDetail) {
                if ($oldDetail->purchase_line_id !== '0' && $oldDetail->purchase_line_id !== null) {

                    $purchaseLine = PurchaseLines::find($oldDetail->purchase_line_id);

                    if ($purchaseLine) {
                        $purchaseLine->quantity += $oldDetail->quantity;   // return stock
                        $purchaseLine->sold_quantity -= $oldDetail->quantity;
                        $purchaseLine->save();
                    }
                }
            }

            // Delete old sale details
            $previousSaleDetails->each->delete();

            $itemPurchaseAmount = 0;
            foreach ($validator->validated()['products'] as $product) {
                $calculatedCost = $product['unit_price'] * $product['quantity'];
                if (round($calculatedCost, 2) !== round($product['cost'], 2)) {
                    throw new \Exception("Invalid cost for {$product['item_name']}. Expected: {$calculatedCost}, Got: {$product['cost']}");
                }

                $purchaseLine = null;
                if ($product['item_id'] !== '0' && $product['item_id'] !== null) {
                    $purchaseLine = PurchaseLines::lockForUpdate()->findOrFail($product['item_id']);
                }

                if ($purchaseLine) {
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
                    'item_actual_price' => $product['item_actual_price'],
                    'vehicle_id' => $product['item_type'] === 'car_spare_part' ? ($product['vehicle_id'] ?? null) : null,
                    'item_type' => $product['item_type'],
                    'item_name' => trim(preg_replace('/\s+/', ' ', $product['item_name'])),
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'cost' => $product['cost'],
                    'updated_by' => auth()->id(),
                ]);

                $itemPurchaseAmount += $product['item_actual_price'];
            }

            $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

            $newPaidAmount = $validator->validated()['paid_amount'] ?? 0;

            $balanceChange = $newPaidAmount - $oldPaidAmount;

            if ($balanceChange > 0) {
                // Company received more payment
                $balance->increment('balance', $balanceChange);
                $balance->refresh();
            } elseif ($balanceChange < 0) {
                // Company refunded or got less payment
                $balance->decrement('balance', abs($balanceChange));
                $balance->refresh();
            }

            // Delete old transactions
            $sale->transactions->each->delete();

            // Create new transaction
            Transaction::create([
                'trxid' => 'TRX-' . Str::uuid(),
                'type' => 'sale',
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'transaction_date' => $validator->validated()['date'],
                'final_amount' => $validator->validated()['final_amount'],
                'status'           => $validator->validated()['final_amount'] > $validator->validated()['paid_amount'] ? 'partially_paid' : 'paid',
                'note' => 'Sale transaction for ' . ($validator->validated()['customer'] ? 'customer ID: ' . $validator->validated()['customer'] : 'Walking Customer'),
                'created_by' => auth()->id(),
                'payment_method'   => $validator->validated()['payment_method'],
                'bank_id'          => $validator->validated()['bank_id'],
                'paid_amount'      => $validator->validated()['paid_amount'] ?? 0,
                'current_balance'  => $balance->balance,
                'branch_id'        => active_branch_id(),
            ]);


            $sale->update([
               'paid_amount' => $validator->validated()['paid_amount'] ?? 0,
               'item_purchase_amount' => $itemPurchaseAmount
            ]);

            DB::commit();

            if (request()->ajax()) {
                    $sale->load(['customer', 'items', 'transactions']);
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

            return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
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

    public function delete($id)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        try {
            DB::beginTransaction();

            $sale = Sale::with('items')->findOrFail($id);

            // Restore stock for every stock-backed item type (spare_part and
            // car_spare_part both reference a purchase line).
            $sale->items->each(function ($item) {
                if (in_array($item->item_type, ['spare_part', 'car_spare_part'])
                    && $item->purchase_line_id !== '0'
                    && $item->purchase_line_id !== null) {
                    $purchaseLine = PurchaseLines::lockForUpdate()->find($item->purchase_line_id);

                    if ($purchaseLine) {
                        $purchaseLine->quantity += $item->quantity;   // return stock
                        $purchaseLine->sold_quantity -= $item->quantity;
                        $purchaseLine->save();
                    }
                }
            });

            // Reverse the sale's NET cash effect on the branch balance:
            // - non-refund transactions put cash IN  -> take it back out
            // - refund transactions took cash OUT    -> put it back in
            $transactions = Transaction::where('sale_id', $sale->id);
            $cashIn  = (clone $transactions)->where('type', '!=', 'refund')->sum('paid_amount') ?? 0;
            $cashOut = (clone $transactions)->where('type', 'refund')->sum('paid_amount') ?? 0;
            $balance->decrement('balance', $cashIn);
            $balance->increment('balance', $cashOut);
            SaleDetails::where('sale_id', $sale->id)->delete();
            $transactions->delete();
            $sale->update(['deleted_by' => auth()->id()]);
            $sale->delete();

            DB::commit();
            return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Sale deleted successfully!'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->logError('delete', $e);
            return response()->json([
                'type' => 'error',
                'title' => __('Error'),
                'msg' => __('An error occurred while deleting the sale.'),
            ], 500);
        }
    }

    public function returnForm($id)
    {
        $sale = Sale::with(['customer', 'items', 'items.purchaseLines'])->findOrFail($id);
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
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float) $line->unit_price,
                        'target_sell_price' => $line->target_sell_price,
                        'uid' => $item->uid,
                        'available_quantity' => $item->purchaseLines
                            ->where('unit_price', $line->unit_price)
                            ->sum('quantity'),
                    ];
                });
            })
            ->filter(function ($item) {
                return $item->available_quantity > 0;
            })
            ->values();

        $existingQuantities = $sale->items->whereIn('item_type', ['spare_part', 'car_spare_part'])
            ->where('item_id', '!=', '0')
            ->mapWithKeys(function ($item) {
                $part = PurchaseLines::find($item->purchase_line_id);

                if (!$part) {
                    return [$item->purchase_line_id => 0];
                }
                $availableQuantity = $part->quantity;
                return [$item->purchase_line_id => $availableQuantity + $item->quantity]; // Add back sold quantity
            })->toArray();

        return view('sale.return', compact('sale', 'vehicles', 'parts', 'banks', 'existingQuantities'));
    }

    public function returnSale(Request $request, $id)
    {
        //  Pre-validation balance check
       $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if ($request->filled('paid_amount') && $request->paid_amount > 0) {
            if ($request->paid_amount > $balance->balance) {
                if ($request->ajax()) {
                    return response()->json([
                        "message" => "Balance exceeds, please check available balance.",
                        'errors' => [
                            'paid_amount' => 'Balance exceeds, please check available balance.'
                        ]
                    ], 403);
                }

                return back()->with('error', 'Balance exceeds, please check available balance.');
            }
        }

        $validator = Validator::make($request->all(), [
            'return_items.*' => ['required'],
            'return_items.*.sale_detail_id' => ['required', 'exists:sale_details,id'],
            'return_items.*.quantity' => ['required', 'integer', 'min:1'],
            'return_date' => ['required', 'date'],
            'refund_amount' => ['nullable', 'numeric'],
            'reason' => ['nullable', 'string', 'max:255'],
            'products.*' => ['nullable'],
            'products.*.quantity' => ['nullable', 'integer', 'min:1'],
            'final_amount' => ['nullable', 'numeric'],
            'paid_amount' => ['nullable', 'numeric'],
            'payment_method' => ['required_with:paid_amount|string', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
            'bank_id' => ['nullable', 'exists:banks,id'],
            'vat_type' => ['required', Rule::in(['inclusive', 'exclusive'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sale = Sale::with('items')->findOrFail($id);
        $vat = $sale->vat;


        if (!$request->has('products')) {

            // Map the returned quantities by their sale_detail_id
            $returnQuantities = [];
            foreach ($request->return_items as $item) {
                $returnQuantities[$item['sale_detail_id']] = (int)$item['quantity'];
            }

            // Verify if every single sold item is fully returned
            $isAllItemsReturned = true;

            foreach ($sale->items as $saleDetail) {

                if (!isset($returnQuantities[$saleDetail->id]) || $returnQuantities[$saleDetail->id] < $saleDetail->quantity) {
                    $isAllItemsReturned = false;
                    break;
                }
            }

            // if ($isAllItemsReturned) {
            //     return response()->json([
            //             "message"=> "All items cannot be returned when exchange items are not added.",
            //         ], 401);
            // }
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            $totalRefund = 0;
            $totalRefundWithVat = 0;
            $returnedItems = [];
            $exchangeItems = [];

            /** HANDLE RETURNS */
            if (!empty($validated['return_items'])) {
                foreach ($validated['return_items'] as $returnItem) {
                    $saleDetail = $sale->saleDetails->where('sale_id', $sale->id)
                    ->where('id', $returnItem['sale_detail_id'])
                    ->firstOrFail();

                    if ($returnItem['quantity'] > $saleDetail->quantity) {
                        throw new \Exception("Return quantity for '{$saleDetail->item_name}' exceeds sold quantity.");
                    }

                    $remainingQty = $saleDetail->quantity - $returnItem['quantity'];

                    $totalRefund += ($saleDetail->unit_price * $returnItem['quantity']);

                    // Update purchase line BEFORE deleting sale detail
                    if ($saleDetail->purchase_line_id != 0 && $saleDetail->purchase_line_id != null) {
                        $purchaseLine = PurchaseLines::findOrFail($saleDetail->purchase_line_id);

                        $purchaseLine->quantity += $returnItem['quantity'];      // add stock back
                        $purchaseLine->sold_quantity -= $returnItem['quantity']; // decrease sold qty
                        $purchaseLine->return_quantity += $returnItem['quantity']; // increase returned qty

                        $purchaseLine->save();
                    }

                    // Store returned item details for return list
                    $returnedItems[] = [
                        'id' => $saleDetail->id,
                        'item_name' => $saleDetail->item_name,
                        'quantity' => $returnItem['quantity'],
                        'unit_price' => $saleDetail->unit_price,
                        'cost' => $returnItem['cost'],
                    ];

                    if ($remainingQty === 0) {
                        // now safe to delete
                        $this->createReturnList($saleDetail, $sale, $returnItem);

                        $saleDetail->delete();

                    } else {

                        $saleDetail->update([
                            'quantity' => $remainingQty,
                            'cost' => $saleDetail->unit_price * $remainingQty,
                            'updated_by' => auth()->id(),
                        ]);

                        $this->createReturnList($saleDetail, $sale, $returnItem);
                    }
                }

                // $totalRefundWithVat = $totalRefund + ($totalRefund * $vat);
                // $balance->balance -= $totalRefundWithVat;
                // $balance->save();

                // $refundTrx = Transaction::create([
                //     'trxid' => 'REF-' . Str::uuid(),
                //     'type' => 'payment',
                //     'sale_id' => $sale->id,
                //     'customer_id' => $sale->customer_id,
                //     'transaction_date' => $validated['return_date'],
                //     'final_amount' => NULL,
                //     'paid_amount' => $totalRefundWithVat,
                //     'status' => 'completed',
                //     'payment_method' => $validated['payment_method'],
                //     'bank_id' => $validated['bank_id'],
                //     'note' => "Refund for Sale #{$sale->id} | Reason: {$validated['reason']}",
                //     'created_by' => auth()->id(),
                //     'current_balance' => $balance->balance,
                // ]);
            }


            // HANDLE EXCHANGES
            // products = exhcange items
            if (!empty($validated['products'])) {
                $totalExchangeCost = 0;

                foreach ($validated['products'] as $exchangeItem) {
                    $newItemCalculatedCost = ($exchangeItem['unit_price'] * $exchangeItem['quantity']) ?? 0;

                    // Pick an in-stock purchase line of this part in the active
                    // branch (locked), instead of blindly taking the first row.
                    $purchaseLine = null;
                    if (!empty($exchangeItem['item_id']) && $exchangeItem['item_id'] != 0) {
                        $purchaseLine = PurchaseLines::lockForUpdate()
                            ->where('vehicle_detail_id', $exchangeItem['item_id'])
                            ->where('quantity', '>=', $exchangeItem['quantity'])
                            ->orderBy('quantity', 'desc')
                            ->first();
                    }

                    $exchangeProduct = SaleDetails::create([
                        'sale_id'         => $sale->id,
                        'item_id'         => $purchaseLine->vehicle_detail_id ?? 0,
                        'purchase_line_id' => $purchaseLine->id ?? 0,
                        'vehicle_id'      => $exchangeItem['item_type'] === 'car_spare_part' ? ($exchangeItem['vehicle_id'] ?? null) : null,
                        'item_type'       => $exchangeItem['item_type'],
                        'item_name'       => trim(preg_replace('/\s+/', ' ', $exchangeItem['item_name'])),
                        'unit_price'      => $exchangeItem['unit_price'],
                        'item_actual_price' => $purchaseLine->unit_price ?? 0,
                        'quantity'        => $exchangeItem['quantity'],
                        'cost'            => $newItemCalculatedCost,
                        'branch_id'       => active_branch_id(),
                        'created_by'      => auth()->id(),
                    ]);

                    $exchangeItems[] = $exchangeProduct;

                    // Deduct stock
                    if (!empty($exchangeItem['item_id']) && $exchangeItem['item_id'] != 0) {

                        if (!$purchaseLine) {
                            throw new \Exception("Insufficient stock for exchange item '{$exchangeItem['item_name']}'.");
                        }

                        $purchaseLine->quantity -= $exchangeItem['quantity'];
                        $purchaseLine->sold_quantity += $exchangeItem['quantity'];
                        $purchaseLine->save();
                    }

                    $totalExchangeCost += $newItemCalculatedCost;
                }


                $balance->balance += $totalRefundWithVat;
                $balance->save();

                $exchangeTrx= Transaction::create(attributes: [
                    'trxid' => 'TRX-' . Str::uuid(),
                    'type' => 'sale',
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'transaction_date' => $validated['return_date'],
                    'final_amount' => $totalExchangeCost + ($totalExchangeCost * $vat),
                    'paid_amount' => $totalRefundWithVat,
                    'status' => 'completed',
                    'payment_method' => $validated['payment_method'],
                    'bank_id' => $validated['bank_id'],
                    'note' => "Exchange for Sale #{$sale->id}",
                    'created_by' => auth()->id(),
                    'current_balance' => $balance->balance, //22815 + 400 = 23215
                    'branch_id' => active_branch_id(),
                ]);

            }


            // HANDLE PAYMENT
            if($request->paid_amount > 0){
                $balance->balance  += $request->paid_amount;
                $balance->save();

                Transaction::create(attributes: [
                    'trxid' => 'TRX-' . Str::uuid(),
                    'type' => 'payment',
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'transaction_date' => $validated['return_date'],
                    'final_amount' => NULL,
                    'paid_amount' => $request->paid_amount,
                    'status' => 'completed',
                    'payment_method' => $validated['payment_method'],
                    'bank_id' => $validated['bank_id'],
                    'note' => "Payment for Sale #{$sale->id}",
                    'created_by' => auth()->id(),
                    'current_balance' => $balance->balance,
                    'branch_id' => active_branch_id(),
                ]);

            }else if($request->refund_amount > 0){
            // Refund includes the VAT portion (the customer paid VAT on these
            // goods at sale time), mirroring how the sale charged them.
            $totalRefundWithVat = $totalRefund + ($totalRefund * $vat);

            // Capped by the value of goods actually returned in this request
            // (goods + VAT) and by the cash currently held by the branch.
            $refundable = min($totalRefundWithVat, $balance->balance);

            if ($request->refund_amount > $refundable) {
                throw new \Exception("Refund amount exceeds refundable amount. Refundable (goods + VAT): {$refundable}");
            }

            $balance->balance  -= $request->refund_amount;
                $balance->save();

                Transaction::create(attributes: [
                    'trxid' => 'TRX-' . Str::uuid(),
                    'type' => 'refund',
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'transaction_date' => $validated['return_date'],
                    'final_amount' => NULL,
                    'paid_amount' => $request->refund_amount,
                    'status' => 'completed',
                    'payment_method' => $validated['payment_method'],
                    'bank_id' => $validated['bank_id'],
                    'note' => "Return refund for Sale #{$sale->id}",
                    'created_by' => auth()->id(),
                    'current_balance' => $balance->balance,
                    'branch_id' => active_branch_id(),
                ]);

            }


            $transections = Transaction::with('bank')->where('sale_id', $sale->id)->get();
            $totalPayed = $transections->where('type', '!=', 'refund')->sum('paid_amount');
            $totalRefunded = $transections->where('type', 'refund')->sum('paid_amount');
            $salePaidAmount = $totalPayed - $totalRefunded;
            $detailsData = SaleDetails::where('sale_id', $sale->id)->get();
            $totalCost = $detailsData->sum('cost');

            $itemPurchaseAmount = $detailsData->sum(function ($detail) {
                return $detail->item_actual_price * $detail->quantity;
            });


            $vat = $sale->vat;
            $vatAmount = $totalCost * $vat;

            $sale->update([
               'item_purchase_amount' => $itemPurchaseAmount,
               'total_cost' => $totalCost,
               'paid_amount' => $salePaidAmount,
               'vat' => $vat,
               'vat_type' => $validated['vat_type'],
               'vat_amount' => $vatAmount,
               'final_amount' => $totalCost  + $vatAmount
            ]);



            DB::commit();

            if (request()->ajax()) {
                    $sale->refresh();
                    $invoiceNo = $sale->invoice_no ?? $sale->id;
                    $date = $validated['return_date']??now()->format('Y-m-d');
                    $customerName = $sale->customer->name ?? 'Walking Customer';
                    $vat = $sale->vat;

                    $paidAmount = $request->paid_amount ?? 0;
                    $refundAmount = $request->refund_amount ?? 0;
                    $previousDue = $request->previous_due_amount ?? 0;


                    $htmlView = view('sale.return_invoice',
                                compact('sale','date','invoiceNo','customerName','returnedItems','exchangeItems', 'vat', 'paidAmount', 'refundAmount', 'previousDue')
                                    )->render();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Sale return & exchange processed successfully.',
                    'view' => $htmlView
                ], 200);
            }

            return redirect()->route('sales.index')->with('success', 'Sale return & exchange processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('returnSale', $e);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function createReturnList($saleDetail, $sale, $returnItem)
    {
        PartsReturnList::create([
            'sale_id' => $sale->id,
            'vehicle_detail_id' => $saleDetail->item_id,
            'item_name' => $saleDetail->item_name,
            'unit_price' => $saleDetail->unit_price,
            'quantity' => $returnItem['quantity'],
            'refund_amount' => $saleDetail->unit_price * $returnItem['quantity'],
            'branch_id' => active_branch_id(),
        ]);
    }

    public function showPayDueForm($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);
        return view('sale.pay_due', compact('sale'));
    }

    public function payDue(Request $request, $id)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        $validator = Validator::make($request->all(), [
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
            'payment_date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($id);
            $dueAmount = $sale->final_amount - $sale->paid_amount;

            if ($validator->validated()['payment_amount'] > $dueAmount) {
                throw new \Exception("Payment amount exceeds due amount. Due: {$dueAmount}");
            }

            $sale->update([
                'paid_amount' => $sale->paid_amount + $validator->validated()['payment_amount'],
                'updated_by' => auth()->id(),
            ]);

            $transaction = Transaction::create([
                'trxid' => 'PAY-' . Str::uuid(),
                'type' => 'payment',
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'transaction_date' => $validator->validated()['payment_date'],
                'payment_method' => $validator->validated()['payment_method'],
                'paid_amount' => $validator->validated()['payment_amount'],
                'status' => 'completed',
                'note' => 'Due payment for Sale #' . $sale->id . ' via ' . $validator->validated()['payment_method'],
                'created_by' => auth()->id(),
                'current_balance' => $balance->balance + $validator->validated()['payment_amount'],
                'branch_id' => active_branch_id(),
            ]);

            $balance->increment('balance', $validator->validated()['payment_amount'] ?? 0);

            DB::commit();

            if (request()->ajax()) {
                $numberToWords = new NumberToWords();
                // Create number transformer for Arabic
                $numberTransformer = $numberToWords->getNumberTransformer('ar');
                $amountInWords =  $numberTransformer->toWords($transaction->paid_amount);
                $transactions[0] = $transaction;

                $transactions = collect($transactions);

                $htmlView = view('sale.pay-slip', compact('transactions','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }
            return redirect()->route('sales.show', $sale->id)->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('payDue', $e);
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
    public function downloadInvoice($id)
    {
        $sale = Sale::with(['customer', 'items'])->findOrFail($id);

        $pdf = Pdf::loadView('sale.slip', compact('sale'));
        return $pdf->download('invoice_' . $sale->invoice_no . '.pdf');
    }
}
