<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Balance;
use App\Models\Customer;
use NumberToWords\NumberToWords;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['sales']);

        // সার্চ অপশন
        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $customers = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();

        $customers->each(function ($customer) {
            // Calculate total due from sales
            $customer->total_paid = 0;
            $customer->final_amount = 0;
            $customer->sales->each(function ($sale) use ($customer) {
                $customer->total_paid += $sale->paid_amount;
                $customer->final_amount += $sale->final_amount;
            });
            $customer->total_due = $customer->final_amount - $customer->total_paid;
        });

        $sl = ($customers->currentPage() - 1) * $perPage;


        return view('customer.index', ['datas' => $customers, 'sl' => $sl]);
    }

    public function payForm(Sale $sale)
    {
        $unpaidInvoices = Sale::where('customer_id', $sale->customer_id)
            ->whereColumn('final_amount', '>', 'paid_amount')
            ->get();

        return view('customer.pay_due', compact('sale', 'unpaidInvoices'));
    }

    public function pay(Request $request)
    {
        $validated = $request->validate([
            'invoices' => 'required|array|min:1',
            'invoices.*.id' => 'required|exists:sales,id',
            'invoices.*.payment' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,due',
            'payment_date' => 'required|date',
            'payment_note' => 'nullable',
        ]);

        $paymentMethod = $validated['payment_method'];
        $paymentDate = $validated['payment_date'];
        $transactions = collect();

        DB::beginTransaction();

        try {
            foreach ($validated['invoices'] as $invoiceData) {
                $sale = Sale::findOrFail($invoiceData['id']);

                // Calculate max due to validate payment does not exceed due amount
                $dueAmount = $sale->final_amount - $sale->paid_amount;

                if ($invoiceData['payment'] > $dueAmount) {
                    return redirect()->back()
                        ->withErrors(['payment_amount' => "Payment amount for Invoice #{$sale->id} exceeds due amount."])
                        ->withInput();
                }

                // Update paid amount
                $sale->paid_amount += $invoiceData['payment'];
                $sale->save();

                $balance = Balance::firstOrCreate([], ['balance' => 0]);
                $balance->increment('balance', $invoiceData['payment']);
                $balance->refresh();

                // Create transaction
                $transactions[] = Transaction::create([
                    'trxid' => 'PAY-' . Str::uuid()->toString(),
                    'type' => 'payment',
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'transaction_date' => $paymentDate,
                    'paid_amount'  => $invoiceData['payment'],
                    'payment_method'  => $paymentMethod,
                    'status' => 'completed',
                    'note' => !empty($validated['payment_note'])
                            ? $validated['payment_note']
                            : 'Due payment for Sale #' . $sale->id . ' via ' . $paymentMethod,
                    'created_by' => auth()->user()->id,
                    'current_balance' => $balance->balance,
                ]);

            }

            DB::commit();

            if (request()->ajax()) {
                    $numberToWords = new NumberToWords();
                    // Create number transformer for Arabic
                    $numberTransformer = $numberToWords->getNumberTransformer('ar');

                    // Generate the words using each item's paid_amount
                    $amountInWords = $numberTransformer->toWords($transactions->sum('paid_amount'));

                    $htmlView = view('sale.pay-slip', compact('transactions','amountInWords'))->render();

                return response()->json([
                    'status' => 'success',
                    'view' => $htmlView
                ], 200);
            }

            return redirect()->route('customer.index')->with('success', 'Payments recorded and transactions created successfully!');
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', 'An error occurred while recording the payments.');
        }
    }

    // public function directPayForm(Customer $customer)
    // {
    //     $total_due = Sale::where('customer_id', $customer->id)
    //         ->whereColumn('final_amount', '>', 'paid_amount')
    //         ->sum(DB::raw('final_amount - paid_amount'));
    //     return view('customer.direct_pay', compact('customer','total_due'));
    // }

    // public function directPay(Request $request)
    // {
    //     $validated = $request->validate([
    //         'customer_id' => 'required|exists:customers,id',
    //         'payment_amount' => 'required|numeric|min:0.01',
    //         'payment_method' => 'required|string|in:Cash,Bank,Cheque',
    //         'payment_date' => 'required|date',
    //         'note' => 'nullable|string|max:255',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         // Create a direct transaction
    //         Transaction::create([
    //             'trxid' => 'PAY-' . Str::uuid()->toString(),
    //             'type' => 'payment',
    //             'customer_id' => $validated['customer_id'],
    //             'sale_id' => null, // Not tied to a specific sale
    //             'transaction_date' => $validated['payment_date'],
    //             // 'final_amount' => $validated['payment_amount'],
    //             'paid_amount'  => $validated['payment_amount'],
    //             'payment_method'  => $validated['payment_method'],
    //             'status' => 'completed',
    //             'note' => $validated['note'] ?? 'Direct payment for Customer #' . $validated['customer_id'] . ' via ' . $validated['payment_method'],
    //             'created_by' => auth()->user()->id,
    //         ]);

    //         $balance = Balance::first();

    //         if ($balance == null){
    //             $balance = new Balance();
    //             $balance->balance = 0;
    //             $balance->save();
    //         }

    //         $balance->increment('balance', $validated['payment_amount']);

    //         DB::commit();
    //         return redirect()->route('customer.index')->with('success', 'Direct payment recorded successfully!');
    //     } catch (\Throwable $th) {
    //         DB::rollback();
    //         Log::error($th->getMessage());
    //         return back()->with('error', 'An error occurred while recording the payment.');
    //     }
    // }

    public function create()
    {
        return view('customer.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'phone' => ['required'],
            'email' => ['nullable'],
            'address' => ['required'],
        ]);

        DB::beginTransaction();

        try {
            $data = new Customer();
            $data->name = $request->name;
            $data->phone = $request->phone;
            $data->email = $request->email;
            $data->address = $request->address;
            $data->created_by = auth()->user()->id;
            $data->save();

            DB::commit();
            return redirect()->route('customer.index')->with('success', 'Customer added successfully');
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', 'An error occurred');
        }
    }

    public function edit($id)
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $data = Customer::findOrFail($id);
        return view('customer.edit', compact('data'));
    }

    public function update(Request $request)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $validated = $request->validate([
            'name' => ['required'],
            'phone' => ['required'],
            'email' => ['nullable'],
            'address' => ['required'],
        ]);

        DB::beginTransaction();

        try {
            $data = Customer::find($request->id);
            $data->name = $request->name;
            $data->phone = $request->phone;
            $data->email = $request->email;
            $data->address = $request->address;
            $data->updated_by = auth()->user()->id;
            $data->save();

            DB::commit();
            return redirect()->route('customer.index')->with('success', 'Customer updated successfully');
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', 'An error occurred');
        }
    }

    public function delete($id)
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }


        DB::beginTransaction();
        try {
            $data = Customer::find($id);
            if (!$data) {
                DB::rollback();
                return $res = [
                    'type' => 'error',
                    'title' => __('Error'),
                    'msg' => __('Data not found!')
                ];
            }

            $data->deleted_by = auth()->user()->id;
            $data->save();
            $data->delete();

            DB::commit();

            return $res = [
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Customer deleted successfully!'),
            ];
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return $res = [
                'type' => 'error',
                'title' => __('Error'),
                'msg' => __('An Error Occurred! Try Again')
            ];
        }
    }

    public function transactions(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d') : Carbon::now()->startOfDay()->format('Y-m-d');

        // Fetch filtered transactions
        $data = Transaction::where('customer_id', $customer->id)
            ->whereBetween('transaction_date', [$from, $to])
            ->select('transaction_date', 'type', 'final_amount', 'paid_amount')
            ->get();

        $preDue = Transaction::where('customer_id', $customer->id)
            ->where('transaction_date', '<', $from)
            ->selectRaw('SUM(final_amount - paid_amount) as due')
            ->first();

        return view('customer.transactions', compact('data', 'customer', 'preDue'));
    }
}
