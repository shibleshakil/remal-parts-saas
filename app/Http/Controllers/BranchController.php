<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all branches with their receivable and payable amounts
        $movements = DB::table('stock_movements')
            ->selectRaw('
                        branch_from_id as from_id,
                        branch_to_id as to_id,
                        SUM(total_amount) as amount
                    ')
            ->groupBy('branch_from_id', 'branch_to_id');

        $ledger = DB::table(DB::raw("({$movements->toSql()}) as m1"))
            ->mergeBindings($movements)
            ->leftJoin(DB::raw("({$movements->toSql()}) as m2"), function ($join) {
                $join->on('m1.from_id', '=', 'm2.to_id')
                    ->on('m1.to_id', '=', 'm2.from_id');
            })
            ->selectRaw('
                        m1.from_id,
                        m1.to_id,
                        (m1.amount - COALESCE(m2.amount, 0)) as net
                    ');

        $relations = DB::table(DB::raw("({$ledger->toSql()}) as l"))
            ->mergeBindings($ledger)
            ->selectRaw('
                    l.from_id,
                    l.to_id,
                    l.net
                ')
            ->where('l.net', '!=', 0)
            ->get();

        $branches = Branch::orderBy('name', 'asc')
            ->leftJoinSub($ledger, 'l1', function ($join) {
                $join->on('branches.id', '=', 'l1.from_id');
            })
            ->leftJoinSub($ledger, 'l2', function ($join) {
                $join->on('branches.id', '=', 'l2.to_id');
            })
            ->selectRaw('
                    branches.*,

                    -- this branch gave stock → others owe this → receivable
                    SUM(CASE WHEN l1.net > 0 THEN l1.net ELSE 0 END) as receivable,

                    -- this branch received stock → it owes others → payable
                    SUM(CASE WHEN l2.net > 0 THEN l2.net ELSE 0 END) as payable,

                    -- final balance
                    (
                        SUM(CASE WHEN l1.net > 0 THEN l1.net ELSE 0 END)
                        -
                        SUM(CASE WHEN l2.net > 0 THEN l2.net ELSE 0 END)
                    ) as net_due
                ')
            ->groupBy('branches.id')
            ->get();


        $branches = $branches->map(function ($branch) use ($relations, $branches) {

            // যারা এই branch কে টাকা দিবে
            $receiveFrom = $relations
                ->where('from_id', $branch->id)
                ->where('net', '>', 0)
                ->map(function ($rel) use ($branches) {
                    $b = $branches->firstWhere('id', $rel->to_id);

                    return $b ? [
                        'id' => $b->id,
                        'name' => $b->name,
                    ] : null;
                })
                ->filter()
                ->values();

            // এই branch যাদের টাকা দিবে
            $payTo = $relations
                ->where('to_id', $branch->id)
                ->where('net', '>', 0)
                ->map(function ($rel) use ($branches) {
                    $b = $branches->firstWhere('id', $rel->from_id);

                    return $b ? [
                        'id' => $b->id,
                        'name' => $b->name,
                    ] : null;
                })
                ->filter()
                ->values();

            $branch->receive_from_branch_id = $receiveFrom;
            $branch->pay_to_branch_id = $payTo;

            return $branch;
        });

        // Pass stock movements (optional detail table)
        $stockMovments = StockMovement::orderBy('created_at', 'desc')
                                        ->with([
                                            'fromBranch',
                                            'toBranch',
                                            'purchaseLine' => function ($q) {
                                                $q->withoutGlobalScope(BranchScope::class)
                                                ->with('vehicleDetail');
                                            }
                                        ])
                                        ->get();

        return view('branch.index', compact('branches', 'stockMovments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('branch.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $branchData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $branchData['code'] = str_pad(Branch::withTrashed()->max('id') + 1, 4, '0', STR_PAD_LEFT);

        Branch::create($branchData);

        return redirect()->route('branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        $branch = Branch::findOrFail($branch->id);

        return view('branch.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $branchData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $branch->update($branchData);

        return redirect()->route('branch.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        if (StockMovement::where('branch_from_id', $branch->id)->orWhere('branch_to_id', $branch->id)->exists()) {
            return response()->json([
                'type' => 'error',
                'title' => __('Error!'),
                'msg' => __('Cannot delete branch with associated stock movements.'),
            ], 400);
        }
        
        $branch->delete();

        return response()->json([
            'type' => 'success',
            'title' => __('Deleted!'),
            'msg' => __('Branch deleted successfully!'),
        ]);
    }


    // Switch branch
    public function switch(Request $request)
    {
        $branchId = $request->branch_id;

        if (isSuperAdmin()) {
            session(['active_branch_id' => $branchId]);
        }

        return back();
    }
}
