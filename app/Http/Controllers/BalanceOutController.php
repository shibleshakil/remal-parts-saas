<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BalanceOutController extends Controller
{
    public function create()
    {
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
        return view('balances.out', compact('banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'bank_id' => 'nullable|required_if:payment_method,bank_transfer|exists:banks,id',
        ]);

        // Validate BEFORE opening a transaction — an early return inside
        // beginTransaction() would abandon an open transaction.
        if($data['final_amount'] > $balance->balance){
            return redirect()->back()->with('error', 'Balance exceeds, please check available balance')->withInput();
        }

        try {
            DB::beginTransaction();

            //AddBalance
            $balance->decrement('balance', $data['final_amount'] ?? 0);
            $balance->refresh();

            // Record transaction
            Transaction::create([
                'trxid' => 'B-OUT-' . Str::uuid(),
                'type' => 'balance_out',
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
            return redirect()->route('balances.index')->with('success', 'Balance out successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('balance-out', $e);
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
        return view('balances.edit-out', compact('transaction', 'banks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // 🔹 Validate request data
        $data = $request->validate([
            'final_amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,due',
            'bank_id' => 'nullable|required_if:payment_method,bank_transfer|exists:banks,id',
        ]);

        // Validate BEFORE opening a transaction (see store()).
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if($data['final_amount'] > $balance->balance){
            return redirect()->back()->with('error', 'Balance exceeds, please check available balance')->withInput();
        }

        try {
            DB::beginTransaction();

            // 🔹 Find existing transaction
            $transaction = Transaction::where('type', 'balance_out')->findOrFail($id);

            // 🔹 Adjust balance if final_amount changed


            if ($transaction->paid_amount != $data['final_amount']) {
                // Subtract old amount and add new one
                $difference = $data['final_amount'] - $transaction->paid_amount;
                $balance->decrement('balance', $difference);
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

            return redirect()->route('balances.index')->with('success', 'Balance out updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('balance-out-update', $e);
            return redirect()->back()->with('error', 'Something went wrong, please try again.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // 🔹 Find the transaction
            $transaction = Transaction::where('type', 'balance_out')->findOrFail($id);

            // 🔹 Reverse the added balance
            $balance = Balance::withoutGlobalScopes()
                                ->firstOrCreate(
                                    ['branch_id' => active_branch_id()],
                                    ['balance' => 0]
                                );

            if ($balance && $transaction->paid_amount > 0) {
                $balance->increment('balance', $transaction->paid_amount ?? 0);
            }

            // 🔹 Delete the transaction record
            $transaction->delete();

            DB::commit();

            // 🔹 Return JSON response
            return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Balance out transaction deleted successfully!'),
            ]);
        }catch (\Exception $e) {
            DB::rollBack();
            $this->logError('balance-out-delete', $e);
            return response()->json([
                'type' => 'error',
                'title' => __('Error!'),
                'msg' => __('Server error!'),
            ]);
        }
    }
}
