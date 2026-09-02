<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Transaction;


class BalanceStatementController extends Controller
{
    public function index()
    {
        if (!(\App\Helpers\CommonHelper::userHasAccess())) {
            if (request()->ajax()) {
                return response()->json([
                    'message' => 'Sorry, you’re not authorized to access this section.'
                ], 403);
            }

            return back()->with(
                'error',
                'Sorry, you’re not authorized to access this section.'
            );
        }

        $from = request('from')
            ? Carbon::parse(request('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = request('to')
            ? Carbon::parse(request('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Types that increase balance
        $balanceInTypes = [
            'sale',
            'payment',
            'return_exchange',
            'balance_add',
            'purchase_return',
        ];

       $baseQuery = Transaction::with([
                'expense' => function ($query) {
                    $query->withTrashed();
                },
                'sale' => function ($query) {
                    $query->withTrashed();
                }
            ])
            ->whereDate('transaction_date', '>=', '2025-12-01');

        $data = (clone $baseQuery)
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->get();

        $previousBalance = (clone $baseQuery)
            ->where('transaction_date', '<', $from)
            ->get()
            ->sum(function ($transaction) use ($balanceInTypes) {
                return in_array($transaction->type, $balanceInTypes)
                    ? $transaction->paid_amount
                    : -$transaction->paid_amount;
            });

        return view('balanceStatement.index', compact(
            'data',
            'previousBalance',
            'balanceInTypes'
        ));
    }
}
