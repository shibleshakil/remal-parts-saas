<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->gross_profit_start_day;
        $endDate = $request->gross_profit_end_day;
        $type = $request->type;


        $query = Transaction::where('branch_id', active_branch_id())->with('bank');

        if ($type && $type != 'all') {
            // Filter by a specific type
            $query->where('type', $type);
        } else {
            // If type is 'all' or not provided, get both balance_add and balance_out
            $query->whereIn('type', ['balance_add', 'balance_out']);
        }

        if ($request->filled('gross_profit_start_day') && $request->filled('gross_profit_end_day')) {
            $query->whereBetween('transaction_date', [
                $startDate,
                $endDate,
            ]);
        }


        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }


        $perPage = session('per_page', 10);

        $transactions = $query->orderBy('transaction_date', 'DESC')->paginate($perPage)->withQueryString();

        $sl = ($transactions->currentPage() - 1) * $perPage;

        return view('balances.index', ['sl' => $sl, 'datas' => $transactions, 'startDate' => $startDate, 'endDate' => $endDate, 'type' => $type]);

    }


    public function create()
    {
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
        return view('balances.create', compact('banks'));
    }


    public function store(Request $request)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        $data = $request->validate([
            'final_amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,due',
            'bank_id'=> 'nullable|required_if:payment_method,bank_transfer|exists:banks,id'
        ]);

        try {
            DB::beginTransaction();
            // AddBalance
            $balance->increment('balance', $data['final_amount']);
            $balance->refresh();

            // Record transaction
            Transaction::create([
                'trxid' => 'B-IN-' . Str::uuid(),
                'type' => 'balance_add',
                'transaction_date' => $data['transaction_date'],
                'final_amount' => 0,
                'paid_amount' => $data['final_amount'],
                'payment_method'=> $data['payment_method'],
                'bank_id' => $data['bank_id'],
                'status' => 'completed',
                'created_by' => auth()->id(),
                'current_balance' => $balance->balance,
                'branch_id' => active_branch_id(),
            ]);


            DB::commit();

            return redirect()->route('balances.index')->with('success', 'Balance in successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('balance-add', $e);
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

    }



    public function edit(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
        return view('balances.edit', compact('transaction', 'banks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        // 🔹 Validate request data
        $data = $request->validate([
            'final_amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,due',
            'bank_id' => 'nullable|required_if:payment_method,bank_transfer|exists:banks,id',
        ]);

        try {
            DB::beginTransaction();
            // 🔹 Find existing transaction
            $transaction = Transaction::where('type', 'balance_add')->findOrFail($id);

            $oldAmount = $transaction->paid_amount;
            $newAmount = $data['final_amount'];

            // 🔹 Recalculate balance change
            $difference = $newAmount - $oldAmount;

            // 🔹 Adjust balance accordingly
            if ($difference != 0) {
                $balance->increment('balance', $difference);
                $balance->refresh();
            }

            // 🔹 Update the transaction record
            $transaction->update([
                'transaction_date' => $data['transaction_date'],
                'final_amount' => 0,
                'paid_amount' => $data['final_amount'],
                'payment_method' => $data['payment_method'],
                'bank_id' => $data['bank_id'],
                'updated_by' => auth()->id(),
                'current_balance' => $balance->balance,
            ]);

            DB::commit();
            return redirect()->route('balances.index')->with('success', 'Balance in updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('balance-add-update', $e);
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }


    public function getBalance(){
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        return response()->json([
            'status' => 'true',
            'balance' => $balance->balance,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // 🔹 Find the transaction
            $transaction = Transaction::where('type', 'balance_add')->findOrFail($id);

            // 🔹 Reverse the added balance
            $balance = Balance::withoutGlobalScopes()
                                ->firstOrCreate(
                                    ['branch_id' => active_branch_id()],
                                    ['balance' => 0]
                                );

            if ($balance && $transaction->paid_amount > 0) {
                $balance->decrement('balance', $transaction->paid_amount ?? 0);
            }

            // 🔹 Delete the transaction record
            $transaction->delete();

            DB::commit();

            // 🔹 Return JSON response
            return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Balance in transaction deleted successfully!'),
            ]);
        }catch (\Exception $e) {
            DB::rollBack();
            $this->logError('balance-in-delete', $e);
            return response()->json([
                'type' => 'error',
                'title' => __('Error!'),
                'msg' => __('Server error!'),
            ]);
        }
    }
}
