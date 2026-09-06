<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\ExpenseListExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Base query
        $query = ExpenseType::query()
            ->with(['expenses' => function ($q) use ($request) {
                // Apply date filter *inside* the relation
                if ($request->filled('FromDate') || $request->filled('ToDate')) {
                    $from = $request->FromDate ?? date('Y-m-d');
                    $to = $request->ToDate ?? date('Y-m-d');
                    $q->whereBetween('expense_date', [$from, $to]);
                }
            }])
            ->withSum(['expenses as total_amount' => function ($q) use ($request) {
                // Also filter the sum by date
                if ($request->filled('FromDate') || $request->filled('ToDate')) {
                    $from = $request->FromDate ?? date('Y-m-d');
                    $to = $request->ToDate ?? date('Y-m-d');
                    $q->whereBetween('expense_date', [$from, $to]);
                }
            }], 'amount');

        //  Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        //  Date filter (only keep expense types that have expenses in date range)
        if ($request->filled('FromDate') || $request->filled('ToDate')) {
            $from = $request->FromDate ?? date('Y-m-d');
            $to = $request->ToDate ?? date('Y-m-d');

            $query->whereHas('expenses', function ($q2) use ($from, $to) {
                $q2->whereBetween('expense_date', [$from, $to]);
            });

            $request['FromDate'] = $from;
            $request['ToDate'] = $to;
        }


        $totalFinalAmount = (clone $query)->get()->sum('total_amount');

        //  Pagination setup
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        $perPage = session('per_page', 10);

        $expenses = $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();

        $sl = ($expenses->currentPage() - 1) * $perPage;

        return view('expense.index', [
            'sl' => $sl,
            'datas' => $expenses,
            'totalFinalAmount' => $totalFinalAmount,
        ]);


    }


    public function create()
    {
        $expenseTypes = ExpenseType::orderBy('name', 'asc')->get();
        return view('expense.create', compact('expenseTypes'));
    }

    public function store(Request $request)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if ($request->amount > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => [
                        'amount' => ['Balance exceeds, please check available balance.']
                    ]
                ], 403);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }

        $validated = $request->validate([
            'expense_type_id' => ['required', 'max:255'],
            'title' => ['required', 'string'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric'],
            'note' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $expense = Expense::create([
                'expense_type_id' => $validated['expense_type_id'],
                'title' => $validated['title'],
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'note' => $validated['note'] ?? null,
                'branch_id' => active_branch_id(),
                'created_by' => auth()->id(),
            ]);

            $balance->decrement('balance', $validated['amount'] ?? 0);
            $balance->refresh();

            Transaction::create([
                'trxid' => 'TRX-' . Str::uuid(),
                'type' => 'expense',
                'expense_id' => $expense->id,
                'transaction_date' => now(),
                'final_amount' => $validated['amount'],
                'paid_amount' => $validated['amount'],
                'status' => 'Paid',
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
                'current_balance' => $balance->balance,
                'branch_id' => active_branch_id(),
            ]);


            DB::commit();
            return redirect()->route('expense.index')->with('success', 'Expense added successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error while storing expense: ' . $th->getMessage(), [
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'An error occurred while saving the expense.');
        }
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $expenseTypes = ExpenseType::orderBy('name', 'asc')->get();
        return view('expense.edit', compact('expense', 'expenseTypes'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $transaction = Transaction::where('expense_id', $expense->id)
                ->whereNull('deleted_at')
                ->first();
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if (($request->amount - $transaction?->paid_amount ) > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => [
                        'amount' => ['Balance exceeds, please check available balance.']
                    ]
                ], 403);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }

        $validated = $request->validate([
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $expense->update([
                'expense_type_id' => $validated['expense_type_id'],
                'title' => $validated['title'],
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'note' => $validated['note'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            $oldPaidAmount = $transaction ? $transaction->paid_amount : 0;
            $newPaidAmount = $validated['amount'] ?? 0;
            // Calculate the net change
            $balanceChange = $newPaidAmount - $oldPaidAmount;
            $balance->balance = $balance->balance - $balanceChange;
            $balance->save();

            if ($transaction) {
                $transaction->update([
                    'type' => 'expense',
                    'transaction_date' => now(),
                    'final_amount' => $validated['amount'],
                    'paid_amount' => $validated['amount'],
                    'status' => 'Paid',
                    'note' => $validated['note'] ?? null,
                    'updated_by' => auth()->id(),
                    'current_balance' => $balance->balance,
                ]);
            } else {
                Transaction::create([
                    'trxid' => 'TRX-' . Str::uuid(),
                    'type' => 'expense',
                    'expense_id' => $expense->id,
                    'transaction_date' => now(),
                    'final_amount' => $validated['amount'],
                    'paid_amount' => $validated['amount'],
                    'status' => 'Paid',
                    'note' => $validated['note'] ?? null,
                    'created_by' => auth()->id(),
                    'current_balance' => $balance->balance,
                ]);
            }

            DB::commit();
            return redirect()->route('expense.index')->with('success', 'Expense updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error while updating expense: ' . $th->getMessage(), [
                'expense_id' => $id,
                'request_data' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $th->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'An error occurred while updating the expense.');
        }
    }


    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $expense = Expense::find($id);

            if (!$expense) {
                DB::rollBack();
                return [
                    'type' => 'error',
                    'title' => __('Error'),
                    'msg' => __('Data not found!'),
                ];
            }

            $transactions = Transaction::where('expense_id', $id)->get();
            $totalPaidAmount = 0;
            foreach ($transactions as $transaction) {
                $totalPaidAmount = $totalPaidAmount + $transaction->paid_amount;
                $transaction->update(['deleted_by' => auth()->id()]);
                $transaction->delete();
            }

            $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

            $balance->balance = $balance->balance + $totalPaidAmount;
            $balance->save();

            $expense->update(['deleted_by' => auth()->id()]);
            $expense->delete();

            DB::commit();

            return [
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Data deleted successfully!'),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error while deleting expense: ' . $th->getMessage());
            return [
                'type' => 'error',
                'title' => __('Error'),
                'msg' => __('An error occurred! Try again.'),
            ];
        }
    }

        public function expensesPrint(Request $request)
        {
           $query = ExpenseType::query()
            ->with(['expenses' => function ($q) use ($request) {
                // Apply date filter *inside* the relation
                if ($request->filled('FromDate') || $request->filled('ToDate')) {
                    $from = $request->FromDate ?? date('Y-m-d');
                    $to = $request->ToDate ?? date('Y-m-d');
                    $q->whereBetween('expense_date', [$from, $to]);
                }
            }])
            ->withSum(['expenses as total_amount' => function ($q) use ($request) {
                // Also filter the sum by date
                if ($request->filled('FromDate') || $request->filled('ToDate')) {
                    $from = $request->FromDate ?? date('Y-m-d');
                    $to = $request->ToDate ?? date('Y-m-d');
                    $q->whereBetween('expense_date', [$from, $to]);
                }
            }], 'amount');

            //  Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            //  Date filter (only keep expense types that have expenses in date range)
            if ($request->filled('FromDate') || $request->filled('ToDate')) {
                $from = $request->FromDate ?? date('Y-m-d');
                $to = $request->ToDate ?? date('Y-m-d');

                $query->whereHas('expenses', function ($q2) use ($from, $to) {
                    $q2->whereBetween('expense_date', [$from, $to]);
                });

                $request['FromDate'] = $from;
                $request['ToDate'] = $to;
            }

            $datas = $query->orderBy('name', 'ASC')->get();

            $title ='Expense List';

            return view('expense.print', compact('datas', 'title'));
        }

            public function expensesExport(Request $request)
            {
                $search = $request->search;
                $fromDate = $request->FromDate;
                $toDate = $request->ToDate;

                return Excel::download(
                    new ExpenseListExport($search, $fromDate, $toDate),
                    'expense_list.xlsx'
                );
            }

    }
