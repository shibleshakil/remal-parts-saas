<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Branch;
use App\Models\PurchaseLines;
use App\Models\Sale;
use App\Models\SaleDetails;
use App\Models\Scopes\BranchScope;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\VehicleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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

        $groupedMovements = $stockMovments
                            ->groupBy(function ($item) {
                                return $item->branch_from_id . '-' . $item->branch_to_id;
                            })
                            ->map(function ($items) {

                                $first = $items->first();

                                return [
                                    'from_branch_id' => $first->branch_from_id,
                                    'to_branch_id' => $first->branch_to_id,
                                    'from_branch_name' => $first->fromBranch?->name,
                                    'to_branch_name' => $first->toBranch?->name,
                                    'total_qty' => $items->sum('quantity'),
                                    'total_amount' => $items->sum('total_amount'),
                                    'items' => $items->values(), // for modal
                                ];
                            })
                            ->values();

        return view('stock-movment.index', compact('groupedMovements','stockMovments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::orderBy('name', 'asc')->get();


        return view('stock-movment.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datas =$request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id',
            'parts_ids' => 'required|array',
            'parts_ids.*' => 'exists:purchase_lines,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        $totalCost = 0;

        // getting balance for the receiving branch
        $toBranchbalance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => $datas['to_branch_id']],
                                ['balance' => 0]
                            );

        // getting balance for the sending branch
        $fromBranchbalance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => $datas['from_branch_id']],
                                ['balance' => 0]
                            );

        $fromBranch = Branch::find($datas['from_branch_id']);
        $toBranch = Branch::find($datas['to_branch_id']);

        $year = date('Y');
        $count = PurchaseLines::withTrashed()
            ->whereYear('purchase_date', $year)
            ->where('bulk_purchase', true)
            ->count() + 1;
        $invoiceNo = 'SST' . $year . $count .'-B' . $datas['to_branch_id'];

        if (count($datas['parts_ids']) > 1) {
                $invoiceNo = 'BUST' . $year . $count . '-B' . $datas['to_branch_id'];
            }

        // create a transaction record for the stock receiving branch
        $transferTransaction = Transaction::create([
            'trxid' => 'SM-' . Str::uuid(),
            'type' => 'stock_movement',
            'transaction_date' => now(),
            'final_amount' => 0,
            'transaction_type' => 'stock_movement',
            'status' => 'unpaid',
            'note' => 'Stock movement from ' . $fromBranch->name . ' to ' . $toBranch->name,
            'created_by' => auth()->id(),
            'branch_id' => $datas['to_branch_id'],
            'current_balance' => $toBranchbalance->balance,
            'purchase_invoice_no' => $invoiceNo,
        ]);

        // create a sale record for the sending branch
        $sale = Sale::create([
                'date' => now(),
                'total_cost' => $totalCost ?? 0,
                'final_amount' => $totalCost ?? 0,
                'branch_id' => $datas['from_branch_id'],
                'created_by' => auth()->id(),
                'item_purchase_amount' => $totalCost ?? 0,
                'paid_amount' => 0,
            ]);


        // create a transaction record for the sending branch
        $saleTransaction = Transaction::create([
            'trxid' => 'TRX-' . Str::uuid(),
            'type' => 'sale_stock_movement',
            'sale_id' => $sale->id,
            'transaction_date' => now(),
            'branch_id' => $datas['from_branch_id'],
            'final_amount' => $totalCost ?? 0,
            'current_balance' => $fromBranchbalance->balance,
            'status' => 'unpaid',
            ]);


        // Process the validated data
        foreach ($datas['parts_ids'] as $index => $partId) {
            $quantity = $datas['quantities'][$index];

            $part = PurchaseLines::withoutGlobalScope(BranchScope::class)->find($partId);

            $totalCost += $quantity * ($part->unit_price ?? 0);

            if (!$part) {
                return redirect()->back()->with('error', 'Invalid part ID.');
            }

            if ($quantity > $part->quantity) {
                return redirect()->back()->with('error', 'Quantity for part ID ' . $part->vehicleDetail?->parts_name . ' exceeds available stock.');
            }


            $finalAmount = intval($quantity) * intval($part->unit_price?? 0);

            // Create a new sale detail record for the sending branch
            $saleDetail = SaleDetails::create([
                'sale_id' => $sale->id,
                'item_id' => $part['vehicle_detail_id'] ?? $partId,
                'purchase_line_id' =>  $partId,
                'item_actual_price' => $part['unit_price'] ?? 0,
                'vehicle_id' => null,
                'item_type' => 'spare_part',
                'item_name' => $part->vehicleDetail?->parts_name?? '',
                'unit_price' => $part['unit_price'],
                'quantity' => $quantity,
                'cost' => $part['unit_price'] * $quantity,
                'created_by' => auth()->id(),
                'branch_id' => $datas['from_branch_id'],
            ]);

            // Create a new stock movement record
            $stockMove = StockMovement::create([
                'unit_price' => intval($part->unit_price),
                'quantity' => intval($quantity),
                'total_amount' => $finalAmount,
                'purchase_line_id' => intval($partId),
                'branch_to_id' => intval($datas['to_branch_id']),
                'branch_from_id' => $part->branch_id,
                'created_by' => auth()->id(),
                'transaction_id' => $transferTransaction->id,
            ]);

            // create a new purchase line for the receiving branch
            $purchaseLine = PurchaseLines::create([
                'vehicle_detail_id' => $part->vehicle_detail_id,
                'quantity' => $quantity,
                'unit_price' => $part->unit_price,
                'purchase_date' => now(),
                'transaction_id' => $transferTransaction->id,
                'target_sell_price' => $part->target_sell_price ?? $part->unit_price,
                'supplier_id' => null,
                'invoice_no' => $invoiceNo,
                'bulk_purchase' => true,
                'attachment' => null,
                'branch_id' => $datas['to_branch_id'],
            ]);

            // Update the quantity of the part
            $part->quantity -= $quantity;
            $part->sold_quantity += $quantity;
            $part->save();
        }

        // Update the transaction
        $transferTransaction->update([
            'final_amount' => $totalCost,
        ]);


        // Update the sale record for the sending branch with the total cost
        $sale->update([
            'total_cost' => $totalCost ?? 0,
            'final_amount' => $totalCost ?? 0,
            'item_purchase_amount' => $totalCost ?? 0,
        ]);


        // Update the transaction record for the sending branch
        $saleTransaction->update([
            'final_amount' => $totalCost ?? 0,
            'note' => 'due ' . $totalCost . ' for stock movement from ' . $fromBranch->name . ' to ' . $toBranch->name,
        ]);

        DB::commit();

        return redirect()->route('stock-movment.index')->with('success', 'Stock moved successfully.');
    }


    public function getPartsByBranch($branch_id)
    {
        $parts = VehicleDetails::withoutGlobalScope(BranchScope::class)
            ->whereNull('vehicle_id')
            ->with(['purchaseLines' => function ($q) use($branch_id) {
                $q->withoutGlobalScope(BranchScope::class)
                ->where('branch_id', $branch_id)
                ->select('id', 'vehicle_detail_id', 'unit_price', 'quantity', 'target_sell_price', 'branch_id');
            }])
            ->get()
            ->flatMap(function ($item) {
                return $item->purchaseLines->map(function ($line) use ($item) {
                    return (object)[
                        'id' => $line->id,
                        // 'purchase_id' => $line->id,
                        'parts_name' => $item->parts_name,
                        'car_name' => $item->car_name,
                        'car_model' => $item->car_model,
                        'unit_price' => (float)$line->unit_price,
                        'target_sell_price' => $line->target_sell_price,
                        'uid' => $item->uid,
                        'available_quantity' => (int) $line->quantity,
                    ];
                });
            });

        return response()->json($parts);
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockMovement $stockMovement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockMovement $stockMovement)
    {
        //
    }
}
