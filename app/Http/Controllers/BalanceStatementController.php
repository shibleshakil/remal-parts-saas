<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Balance;
use App\Models\Transaction;


class BalanceStatementController extends Controller
{
    public function index()
    {

        $from = request('from')
            ? Carbon::parse(request('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = request('to')
            ? Carbon::parse(request('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Enforce server-side: the statement never goes before 2025-12-01,
        // even if someone bypasses the frontend min="" attribute.
        $minDate = Carbon::parse('2025-12-01')->startOfDay();
        $from = $from->max($minDate);
        $to = $to->max($minDate);

        // Types that increase balance
        $balanceInTypes = [
            'sale',
            'payment',
            'return_exchange',
            'balance_add',
            'purchase_return',
        ];

        // Anchor the statement to the single source of truth (per-branch
        // balances table) instead of a hardcoded date cutoff: opening balance
        // = current balance minus the net movement of everything from $from
        // onward (the shown window plus anything dated after it).
        $currentBalance = (float) Balance::withoutGlobalScopes()
            ->firstOrCreate(
                ['branch_id' => active_branch_id()],
                ['balance' => 0]
            )->balance;

        $netFromOnward = Transaction::where('transaction_date', '>=', $from)
            ->get(['type', 'paid_amount'])
            ->sum(function ($transaction) use ($balanceInTypes) {
                $amount = (float) $transaction->paid_amount;

                return in_array($transaction->type, $balanceInTypes) ? $amount : -$amount;
            });

        $previousBalance = round($currentBalance - $netFromOnward, 2);

        $data = Transaction::with([
                'expense' => function ($query) {
                    $query->withTrashed();
                },
                'sale' => function ($query) {
                    $query->withTrashed();
                },
                'customer:id,name',
                'supplier:id,name',
            ])
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        return view('balanceStatement.index', compact(
            'data',
            'previousBalance',
            'balanceInTypes'
        ));
    }
}
