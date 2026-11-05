<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Balance;
use App\Models\PurchaseLines;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use NumberToWords\NumberToWords;

class SupplierController extends Controller
{
    public function index(Request $request){
        $query = Supplier::query();

        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->Where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
            });
        }


        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }


        $perPage = session('per_page', 10);

        $suppliers = $query->orderBy('id', 'DESC')
            ->paginate($perPage)
            ->withQueryString();

        foreach ($suppliers as $supplier) {
            // Due follows INVOICES (same rule as the pay page and pay()
            // validation): every transaction on an invoice that has at least
            // one purchase line for this supplier, plus supplier-tagged rows
            // without an invoice. This keeps listing and pay page identical.
            $invoiceNos = PurchaseLines::where('supplier_id', $supplier->id)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('invoice_no')
                ->filter();
            $sums = Transaction::whereNull('deleted_at')
                ->where(function ($q) use ($invoiceNos, $supplier) {
                    if ($invoiceNos->isNotEmpty()) {
                        $q->whereIn('purchase_invoice_no', $invoiceNos)
                            ->orWhere(function ($qq) use ($supplier) {
                                $qq->where('supplier_id', $supplier->id)
                                    ->whereNull('purchase_invoice_no');
                            });
                    } else {
                        $q->where('supplier_id', $supplier->id);
                    }
                })
                ->selectRaw('SUM(COALESCE(final_amount, 0)) as tf, SUM(COALESCE(paid_amount, 0)) as tp')
                ->first();

            $supplier->total_purchase_amount = (float) $sums->tf;
            $supplier->total_paid = (float) $sums->tp;
            $supplier->due_amount = ((float) $sums->tf) - ((float) $sums->tp);
        }
        $sl = ($suppliers->currentPage() - 1) * $perPage;

        return view('supplier.index', ['sl' => $sl, 'datas' => $suppliers]);
    }

    public function create(){
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:suppliers,phone',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $supplierData = $validator->validated();
        $supplierData['created_by'] = auth()->user()->id;


        $supplier = Supplier::create($supplierData);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier added successfully!',
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('supplier.index')->with("Success","Created Successfully");
    }

    public function edit(string $id)
    {
        $data = Supplier::find($id);
        return view('supplier.edit',['data'=>$data]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
        ]);

        Supplier::find($id)->update($data);
        return redirect()->route('supplier.index')->with('Success','Updated Successfully');
    }

    public function destroy($id)
    {
        Supplier::find($id)->delete();
                return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Supplier deleted successfully!'),
        ]);
    }

    public function payForm($id){
        $pl = PurchaseLines::where('supplier_id', $id)
            ->select('id', 'supplier_id', 'invoice_no', 'bulk_purchase', 'vehicle_detail_id', 'quantity')
            ->with('transactions:id,purchase_invoice_no,type,final_amount,paid_amount')
            ->groupBy('invoice_no')
            ->get();


            // map transactions to calculate due amount for each purchase line
            $pl = $pl->map(function ($purchaseLine) {
                    $totalFinalAmount = $purchaseLine->transactions->sum('final_amount');
                    $totalPaidAmount = $purchaseLine->transactions->sum('paid_amount');

                    $purchaseLine->invoice_amount = $totalFinalAmount;
                    $purchaseLine->invoice_paid = $totalPaidAmount;
                    $purchaseLine->due_amount = $totalFinalAmount - $totalPaidAmount;

                    return $purchaseLine;
                })->filter(function ($purchaseLine) {
                    return $purchaseLine->due_amount > 0;
                })->values();

        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        return view('supplier.pay', ['purchaseLines' => $pl, 'banks' => $banks]);
    }

    public function pay(Request $request, $supplierId){
        $validated = $request->validate([
            'payment_date' => 'required',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,due',
            'bank_id' => 'required_if:payment_method,bank_transfer',
            'payment_note' => 'nullable',
            'transetions'    => 'required|array|min:1',
            'transetions.*.id' => 'required',
            'transetions.*.due' => 'required',
            'transetions.*.payment' => 'required',
        ]);

        // Balance check BEFORE opening a transaction — an early return inside
        // beginTransaction() would abandon an open transaction.
        $balance = Balance::withoutGlobalScopes()
                        ->firstOrCreate(
                            ['branch_id' => active_branch_id()],
                            ['balance' => 0]
                        );
        $totaPaid = collect($validated['transetions'])->sum('payment');

        if ($totaPaid > $balance->balance) {
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

        DB::beginTransaction();
        try {
            $paymentMethod = $validated['payment_method'];
            $transactions = collect();

            foreach ($validated['transetions'] as $transetionId => $transetionInput) {
                // Calculate max due to validate payment does not exceed due amount
                $dueAmount = Transaction::where('purchase_invoice_no', $transetionId)->sum('final_amount') - Transaction::where('purchase_invoice_no', $transetionId)->sum('paid_amount');


                if ($transetionInput['payment'] > $dueAmount) {
                    // Throw (never return) inside an open transaction so the
                    // catch block rolls back ALL invoices of this request.
                    throw new \Exception("Payment amount for Invoice #{$transetionId} exceeds due amount.");
                }


                $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );
                $balance->decrement('balance', $transetionInput['payment'] ??0 );
                $balance->refresh();

                // Create transaction
                $transaction = Transaction::create([
                    'paid_amount' => $transetionInput['payment'],
                    'supplier_id' => $supplierId,
                    'payment_method' => $paymentMethod,
                    'bank_id' => $request->bank_id??null,
                    'transaction_date' => $request->payment_date,
                    'trxid' => 'SUP-' . Str::uuid()->toString(),
                    'type' => 'supplier_payment',
                    'vehicle_id' => $transetionInput['vehicle_id'] ?? null,
                    'vehicle_details_id' => $transetionInput['vehicle_details_id'] ?? null,
                    'sale_id' => null, // Not tied to a specific sale
                    'status' => 'completed',
                    'current_balance' => $balance->balance,
                    'note' => $validated['payment_note']??null,
                    'created_by' => auth()->user()->id,
                    'purchase_invoice_no' => $transetionId??null,
                    'branch_id' => active_branch_id(),
                ]);

                $transactions[] = $transaction;

            }

            DB::commit();

            if (request()->ajax()) {
                    $numberToWords = new NumberToWords();
                    // Create number transformer for Arabic
                    $numberTransformer = $numberToWords->getNumberTransformer('ar');

                    // Generate the words using each item's paid_amount
                    $amountInWords = $numberTransformer->toWords($transactions->sum('paid_amount'));

                    $htmlView = view('supplier.pay-slip', compact('transactions','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }

            return redirect()->route('supplier.index')->with('Success', 'Payment Added Successfully');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $th->getMessage(),
                ], 422);
            }

            return back()->withErrors(['payment_amount' => $th->getMessage()])->withInput();
        }
    }

    public function transactions(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d') : Carbon::now()->startOfDay()->format('Y-m-d');

        // Same invoice-wide rule as the listing and the pay page: every
        // transaction on an invoice that has at least one purchase line for
        // this supplier, plus supplier-tagged rows without an invoice.
        $invoiceNos = PurchaseLines::where('supplier_id', $supplier->id)
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('invoice_no')
            ->filter();

        $scope = function ($q) use ($invoiceNos, $supplier) {
            $q->where(function ($qq) use ($invoiceNos, $supplier) {
                if ($invoiceNos->isNotEmpty()) {
                    $qq->whereIn('purchase_invoice_no', $invoiceNos)
                        ->orWhere(function ($x) use ($supplier) {
                            $x->where('supplier_id', $supplier->id)
                                ->whereNull('purchase_invoice_no');
                        });
                } else {
                    $qq->where('supplier_id', $supplier->id);
                }
            });
        };

        // Fetch filtered transactions (chronological + id for a stable balance)
        $data = Transaction::whereNull('deleted_at')
            ->where($scope)
            ->whereBetween('transaction_date', [$from, $to])
            ->select('id', 'transaction_date', 'type', 'final_amount', 'paid_amount', 'purchase_invoice_no')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // COALESCE: payment/return rows have NULL final_amount and must not
        // silently vanish from the opening-due sum.
        $preDue = Transaction::whereNull('deleted_at')
            ->where($scope)
            ->where('transaction_date', '<', $from)
            ->selectRaw('SUM(COALESCE(final_amount, 0) - COALESCE(paid_amount, 0)) as due')
            ->first();

        return view('supplier.transactions', compact('data', 'supplier', 'preDue'));
    }
}
