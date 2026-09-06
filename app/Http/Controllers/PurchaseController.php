<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\Bank;
use App\Models\PurchaseLines;
use App\Models\PurchasePartsReturnList;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Models\VehicleDetails;
use Illuminate\Http\Request;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseLines::whereNotNull('invoice_no')->with('transactions', 'supplier', 'vehicleDetail.vehicle');

        // সার্চ অপশন
        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', $search);
            });
        }


        // date filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $query->where(function ($q) use ($from_date, $to_date) {
                $q->whereBetween('purchase_date', [$from_date, $to_date]);
            });
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $purchases = $query->orderBy('id', 'DESC')
            ->groupBy('invoice_no')->paginate($perPage)->withQueryString();

        $sl = ($purchases->currentPage() - 1) * $perPage;

        return view('purchase.index', ['datas' => $purchases, 'sl' => $sl]);
    }

    public function create()
    {
        $suppliers = Supplier::select('id', 'name')->orderBy('name', 'asc')->get();
        $vehicles = Vehicle::with('details')->select('id', 'name', 'uid')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
        $carNames = VehicleDetails::pluck('car_name')->unique();

        $parts = VehicleDetails::whereNull('vehicle_id')
            ->select('id', 'parts_name', 'uid','car_name', 'car_model')
            ->orderBy('parts_name', 'asc')
            ->get();

        return view('purchase.create', compact('suppliers', 'vehicles', 'banks', 'parts','carNames'));
    }

    public function store(Request $request)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if ($request->paid_amount > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    "message"=> "Balance exceeds, please check available balance.",
                    'errors' => [
                        'paid_amount' => 'Balance exceeds, please check available balance.'
                    ]
                ], 403);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }

        $validated = $request->validate([
            'purchase_date'         => 'nullable|date',
            'supplier_id'           => 'nullable|exists:suppliers,id',

            // Products array validation
            'products'              => 'required|array|min:1',
            'products.*.item_id'    => 'required|integer|exists:vehicle_details,id',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.quantity'   => 'required|integer|min:1',
            'products.*.target_selling_price' => 'nullable|numeric|min:0',

            // Totals and payments
            'total_cost'           => 'required|numeric|min:0',
            'final_amount'         => 'required|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0|lte:final_amount',
            'payment_method'       => ['nullable','required_with:paid_amount','string', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
            'bank_id'              => 'nullable|exists:banks,id',

            // Optional note
            'note'                 => 'nullable|string|max:1000',
            'attachment.*'          => 'nullable|file|mimes:jpg,png,pdf|max:5120',
        ]);

        $attachmentName = null;

        DB::beginTransaction();
        try {
            if ($request->file('attachment')) {
                $attachmentName = ImageHelper::handleUploadMultipleFile($request->file('attachment'), '/storage/purchase/');
            }

            $balance->decrement('balance', $validated['paid_amount'] ?? 0);
            $balance->refresh();

            $bulkInvoiceNo = '';

            if ($validated['products']) {
                $year = date('Y');
                $count = PurchaseLines::withTrashed()
                    ->whereYear('purchase_date', $year)
                    ->where('bulk_purchase', true)
                    ->count() + 1;

                $bulkInvoiceNo = 'BP' . $year . $count . '-B' . active_branch_id();
            }

            $transaction = Transaction::create([
                'trxid'            => 'TRX-' . Str::uuid(),
                'type'             => 'bulk_purchase',
                'final_amount'     => round($validated['final_amount'], 2),
                'transaction_date' => $validated['purchase_date'],
                'updated_by'       => auth()->id(),
                'supplier_id'      => $validated['supplier_id'],
                'payment_method'   => $validated['payment_method'],
                'bank_id'          => $validated['bank_id'] ?? null,
                'paid_amount'      => $validated['paid_amount'],
                'status'           => $validated['paid_amount'] == 0 ? 'unpaid' : ($validated['paid_amount'] < $validated['final_amount'] ? 'partially_paid' : 'paid'),
                'note'             => $validated['note'] ?? null,
                'current_balance'  => $balance->balance,
                'purchase_invoice_no' => $bulkInvoiceNo,
                'branch_id'         => active_branch_id(),
            ]);

            foreach ($validated['products'] as $index => $item) {
                // Create purchase line
                PurchaseLines::create([
                    'transaction_id'    => $transaction->id,
                    'vehicle_detail_id' => $validated['products'][$index]['item_id'],
                    'quantity'          => $validated['products'][$index]['quantity'],
                    'unit_price'        => round($validated['products'][$index]['unit_price'], 2),
                    'purchase_date'     => $validated['purchase_date'],
                    'target_sell_price' => $validated['products'][$index]['target_selling_price'] ?? null,
                    'supplier_id'       => $validated['supplier_id'],
                    'bulk_purchase'     => true,
                    'invoice_no'        => $bulkInvoiceNo,
                    'attachment'        => $attachmentName,
                    'branch_id'         => active_branch_id(),
                ]);
            }


            DB::commit();

            $purchases = PurchaseLines::where('invoice_no', $bulkInvoiceNo)->with(['transaction'])->get();

            $htmlView = view('purchase.invoice', ['invoice' => $purchases])->render();

            return response()->json([
                'status' => 'success',
                'view' => $htmlView
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($attachmentName) {
                $files  = json_decode($attachmentName, true);
                Log::info('Deleting uploaded files due to error', ['files' => $files]);
                foreach ($files as $file) {
                    $filePath = public_path('/storage/purchase/' . $file);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            Log::error('Error purchasing spare part', ['message' => $th->getMessage()]);
            return response()->json(['error' => 'An error occurred while purchasing the spare part.'], 500);
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
                ->whereNull('vehicle_id')
                ->select('id', 'parts_name', 'uid', 'car_name', 'car_model')
                ->get();

            $success = $parts->isNotEmpty();

            return response()->json([
                'success' => $success,
                'single' => $parts->count() === 1,
                'data' => $parts->count() === 1 ? $parts->first() : $parts,
            ]);
        } catch (\Exception $e) {
            $this->logError('getDataByBarcode', $e);
            return response()->json(['error' => 'Failed to fetch barcode data'], 500);
        }
    }

    public function invoice($invoice_no){
        $data = PurchaseLines::where('invoice_no', $invoice_no)
        ->with(['vehicleDetail', 'transactions','vehicleDetail.vehicle','vehicleDetail.vehicle.purchaseTransaction'])->get();

        return view('purchase.invoice', ['invoice' => $data]);
    }


    public function showPayDueForm($id)
    {
        $purchase = PurchaseLines::where('id', $id)->with('transactions', 'supplier')->first();

        return view('purchase.pay_due', compact('purchase'));
    }

    public function payDue(Request $request, $id)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if ($request->paid_amount > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    "message"=> "Balance exceeds, please check available balance.",
                    'errors' => [
                        'paid_amount' => 'Balance exceeds, please check available balance.'
                    ]
                ], 403);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }

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

            $purchase = PurchaseLines::where('id', $id)->with('transactions')->first();

            if (!$purchase) {
                return back()->with('error', 'Purchase not found');
            }

            $transactions = $purchase->transactions;

            $paidAmount = $transactions->sum('paid_amount') ?? 0;

            $dueAmount = $transactions->sum('final_amount') - $paidAmount;
            $paymentAmount = $validator->validated()['payment_amount'];

            if ($validator->validated()['payment_amount'] > $dueAmount) {
                throw new \Exception("Payment amount exceeds due amount. Due: {$dueAmount}");
            }

            $newTransaction = Transaction::create([
                'paid_amount'        => $paymentAmount,
                'supplier_id'        => $purchase->supplier_id,
                'payment_method'     => $validator->validated()['payment_method'],
                'bank_id'            => $validator->validated()['bank_id'] ?? null,
                'transaction_date'   => $validator->validated()['payment_date'],
                'trxid'              => 'SUP-' . Str::uuid()->toString(),
                'type'               => 'supplier_payment',
                'vehicle_id'         => $transactions[0]->vehicle_id ?? null,
                'vehicle_details_id' => $transactions[0]->vehicle_details_id ?? null,
                'sale_id'            => null,
                'status'             => $paymentAmount == $dueAmount ? 'paid' : 'partially_paid',
                'current_balance'    => $balance->balance - $paymentAmount,
                'note'               => $validator->validated()['payment_note'] ?? null,
                'created_by'         => auth()->id(),
                'purchase_invoice_no'=> $transactions[0]->purchase_invoice_no ?? null,
                'branch_id'          => active_branch_id(),
            ]);

            $balance->decrement('balance', $validator->validated()['payment_amount']);


            DB::commit();

            if (request()->ajax()) {
                $numberToWords = new NumberToWords();
                // Create number transformer for Arabic
                $numberTransformer = $numberToWords->getNumberTransformer('ar');

                // Generate the words using each item's paid_amount
                $amountInWords = $numberTransformer->toWords($newTransaction->paid_amount);
                $transactions[0] = $newTransaction;

                $transactions = collect($transactions);

                $htmlView = view('supplier.pay-slip', compact('transactions','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('payDue', $e);
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function returnPurchase($invoiceNo){
        $purchase = PurchaseLines::where('invoice_no', $invoiceNo)
            ->with('vehicleDetail')
            ->get();

        $canReturn = false;
        foreach ($purchase as $purchaseLine) {
            if ($purchaseLine->quantity) {
                $canReturn = true;
            }
        }

        if(!$canReturn){
            return back()->with('error', 'Purchase line items quantity not available for return.');
        }

        $transection = Transaction::where('purchase_invoice_no', $invoiceNo)->get();

        $vehicles = Vehicle::with('details')->select('id', 'name', 'uid')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        $parts = VehicleDetails::whereNull('vehicle_id')
            ->select('id', 'parts_name', 'uid','car_name', 'car_model')
            ->orderBy('parts_name', 'asc')
            ->get();

        if(!$purchase->count()){
            return back()->with('error', 'Invoice not found');
        }

        return view('purchase.return', compact('purchase', 'vehicles', 'banks', 'parts', 'transection'));
    }


    public function returnPurchasePost(Request $request, $invoiceNo)
    {
        // 1. Pre-validation balance check
        $balance = Balance::firstOrCreate([], ['balance' => 0]);

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

        // 2. Data Validation
        $validator = Validator::make($request->all(), [
            'return_items'                     => ['nullable', 'array'],
            'return_items.*.purchase_line_id'  => ['required_with:return_items', 'exists:purchase_lines,id'],
            'return_items.*.quantity'           => ['required_with:return_items', 'integer', 'min:1'],

            'return_date'                      => ['nullable', 'date'],
            'refund_amount'                    => ['required', 'numeric'],
            'reason'                           => ['nullable', 'string', 'max:255'],

            'products'                         => ['nullable', 'array'],
            'products.*.item_id'               => ['required_with:products', 'exists:vehicle_details,id'],
            'products.*.quantity'              => ['required_with:products', 'numeric'],
            'products.*.unit_price'            => ['required_with:products', 'numeric'],
            'products.*.target_selling_price'  => ['nullable', 'numeric'],

            'paid_amount'                      => ['nullable', 'numeric'],
            'final_amount'                     => ['nullable', 'numeric'],

            'payment_method'                   => ['nullable', 'required_with:paid_amount', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
            'bank_id'                          => ['nullable', 'required_if:payment_method,bank_transfer'],
            'supplier_id'                      => ['nullable', 'exists:suppliers,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();
            $totalReturnAmount = 0;
            $createdReturnItems = [];
            $createdExchangeLines = [];
            $supplierName = '';

            // 3. Process Return Items
            if (!empty($validated['return_items'])) {
                foreach ($validated['return_items'] as $item) {
                    $purchaseLine = PurchaseLines::find($item['purchase_line_id']);
                    $supplierName = $purchaseLine->supplier->name;

                    if (!$purchaseLine || $item['quantity'] > $purchaseLine->quantity) {
                        return response()->json([
                            "message" => "Item not found or available quantity exceeds.",
                        ], 422);
                    }

                    // Add to return trace log
                    $returnList = PurchasePartsReturnList::create([
                        'purchase_line_id' => $purchaseLine->id,
                        'invoice_no'       => $purchaseLine->invoice_no,
                        'item_name'        => $purchaseLine->vehicleDetail->parts_name ?? 'Unknown Part',
                        'unit_price'       => $purchaseLine->unit_price,
                        'quantity'         => $item['quantity'],
                        'return_amount'    => $purchaseLine->unit_price * $item['quantity'],
                    ]);

                    $createdReturnItems[] = $returnList;

                    // Adjust quantities on original stock line
                    $purchaseLine->quantity -= $item['quantity'];
                    $purchaseLine->purchase_return_quantity += $item['quantity'];
                    $purchaseLine->save();

                    $totalReturnAmount += ($item['quantity'] * $purchaseLine->unit_price);
                }

                $balance->increment('balance', $totalReturnAmount);
                $balance->refresh();

                // Record transaction for items going back to supplier
                Transaction::create([
                    'paid_amount'         => $totalReturnAmount,
                    'supplier_id'         => $validated['supplier_id'] ?? null,
                    'payment_method'      => $validated['payment_method'] ?? null,
                    'bank_id'             => $validated['bank_id'] ?? null,
                    'transaction_date'    => $validated['return_date'] ?? now()->format('Y-m-d'),
                    'trxid'               => 'SUP-' . Str::uuid()->toString(),
                    'type'                => 'purchase_return',
                    'status'              => 'completed',
                    'current_balance'     => $balance->balance,
                    'note'                => $validated['reason'] ?? null,
                    'created_by'          => auth()->id(),
                    'purchase_invoice_no' => $invoiceNo,
                ]);
            }

            // 4. Process Exchange / Added Products
            if (!empty($validated['products'])) {
                $exchangeTotal = collect($validated['products'])->sum(function ($product) {
                    return $product['unit_price'] * $product['quantity'];
                });

                $exchangeTransaction = Transaction::create([
                    'final_amount'        => round($exchangeTotal, 2),
                    'paid_amount'         => null,
                    'supplier_id'         => $validated['supplier_id'] ?? null,
                    'payment_method'      => $validated['payment_method'] ?? null,
                    'bank_id'             => $validated['bank_id'] ?? null,
                    'transaction_date'    => $validated['return_date'] ?? now()->format('Y-m-d'),
                    'trxid'               => 'SUP-' . Str::uuid()->toString(),
                    'type'                => 'purchase',
                    'status'              => 'completed',
                    'current_balance'     => $balance->balance,
                    'note'                => $validated['reason'] ?? null,
                    'created_by'          => auth()->id(),
                    'purchase_invoice_no' => $invoiceNo,
                ]);

                foreach ($validated['products'] as $item) {
                    $createdExchangeLines[] = PurchaseLines::create([
                        'transaction_id'    => $exchangeTransaction->id,
                        'vehicle_detail_id' => $item['item_id'],
                        'quantity'          => $item['quantity'],
                        'unit_price'        => round($item['unit_price'], 2),
                        'purchase_date'     => $validated['return_date'] ?? now()->format('Y-m-d'),
                        'target_sell_price' => $item['target_selling_price'] ?? null,
                        'supplier_id'       => $validated['supplier_id'] ?? null,
                        'bulk_purchase'     => true,
                        'invoice_no'        => $invoiceNo,
                    ]);
                }
            }

            // 5. Update system balance if actual cash/bank payment went out
            if (isset($validated['paid_amount']) && $validated['paid_amount'] > 0) {
                $balance->decrement('balance', $validated['paid_amount']);
                $balance->refresh();

                Transaction::create([
                    'paid_amount'         => $validated['paid_amount'],
                    'supplier_id'         => $validated['supplier_id'] ?? null,
                    'payment_method'      => $validated['payment_method'] ?? null,
                    'bank_id'             => $validated['bank_id'] ?? null,
                    'transaction_date'    => $validated['return_date'] ?? now()->format('Y-m-d'),
                    'trxid'               => 'SUP-' . Str::uuid()->toString(),
                    'type'                => 'supplier_payment',
                    'status'              => 'completed',
                    'current_balance'     => $balance->balance,
                    'note'                => $validated['reason'] ?? null,
                    'created_by'          => auth()->id(),
                    'purchase_invoice_no' => $invoiceNo,
                ]);
            }

            DB::commit();

            // 6. Response Handlers
            if (request()->ajax()) {
                $date = $validated['return_date'] ?? now()->format('Y-m-d');

                // Cleaned up view-rendering logic to represent purchases instead of sales
                $htmlView = view('purchase.return_invoice', [
                    'date'          => $date,
                    'invoiceNo'     => $invoiceNo,
                    'returnedItems' => $createdReturnItems,
                    'exchangeItems' => $createdExchangeLines,
                    'refundAmount'  => $validated['refund_amount'],
                    'supplierName'  => $supplierName,
                ])->render();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Purchase return & exchange processed successfully.',
                    'view'    => $htmlView
                ], 200);
            }

            return redirect()->route('purchases.index')->with('success', 'Purchase return & exchange processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return purchase error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
}
