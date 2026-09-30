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

        $query->withSum('transactions as total_purchase_amount', 'final_amount')
            ->withSum('transactions as total_paid', 'paid_amount');

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

        $suppliers = $query->with('transactions')->orderBy('id', 'DESC')
            ->paginate($perPage)
            ->withQueryString();

        foreach ($suppliers as $supplier) {
            $supplier->due_amount = $supplier->total_purchase_amount - $supplier->total_paid;
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

        DB::beginTransaction();
        try {
            $paymentMethod = $validated['payment_method'];
            $totaPaid = collect($validated['transetions'])->sum('payment');

            $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

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

            foreach ($validated['transetions'] as $transetionId => $transetionInput) {
                // Calculate max due to validate payment does not exceed due amount
                $dueAmount = Transaction::where('purchase_invoice_no', $transetionId)->sum('final_amount') - Transaction::where('purchase_invoice_no', $transetionId)->sum('paid_amount');


                if ($transetionInput['payment'] > $dueAmount) {
                    return redirect()->back()
                        ->withErrors(['payment_amount' => "Payment amount for Invoice #{$transetionId} exceeds due amount."])
                        ->withInput();
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
                    'vehicle_id' => $transetionInput->vehicle_id??null,
                    'vehicle_details_id' => $transetionInput->vehicle_details_id??null,
                    'sale_id' => null, // Not tied to a specific sale
                    'status' => 'completed',
                    'current_balance' => $balance->balance,
                    'note' => $validated['payment_note']??null,
                    'created_by' => auth()->user()->id,
                    'purchase_invoice_no' => $transetionId??null,
                    'branch_id' => active_branch_id(),
                ]);

            }

            DB::commit();

            if (request()->ajax()) {
                    $numberToWords = new NumberToWords();
                    // Create number transformer for Arabic
                    $numberTransformer = $numberToWords->getNumberTransformer('ar');

                    // Generate the words using each item's paid_amount
                    $amountInWords = $numberTransformer->toWords($transaction->sum('paid_amount'));
                    $transactions[0] = $transaction;

                    $transactions = collect($transactions);

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
            return back()->with('error', 'An error occured')->withInput($request->all());
        }
    }

    public function transactions(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d') : Carbon::now()->startOfDay()->format('Y-m-d');

        // Fetch filtered transactions — MUST be ordered chronologically,
        // otherwise the running balance in the view accumulates in random
        // DB order and payment rows apply before their purchases.
        $data = Transaction::where('supplier_id', $supplier->id)
            ->whereBetween('transaction_date', [$from, $to])
            ->select('id', 'transaction_date', 'type', 'final_amount', 'paid_amount', 'purchase_invoice_no')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $preDue = Transaction::where('supplier_id', $supplier->id)
        ->where('transaction_date', '<', $from)
        ->selectRaw('SUM(COALESCE(final_amount, 0) - COALESCE(paid_amount, 0)) as due')
        ->first();

        return view('supplier.transactions', compact('data', 'supplier', 'preDue'));
    }
}
