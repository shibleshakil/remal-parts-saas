<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use App\Models\VehicleDetails;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $vehicles = Vehicle::select('id', 'name', 'uid')->orderBy('name', 'ASC')->get();
        $parts = VehicleDetails::select('id', 'parts_name', 'uid')->orderBy('parts_name', 'ASC')->get();
        return view('index' , compact('vehicles', 'parts'));
    }

    public function stockSummary()
    {
        $partsDetails = DB::SELECT("SELECT SUM(CASE WHEN vd.vehicle_id IS NULL THEN pl.quantity ELSE 0 END) AS parts_stock_quantity,
            SUM(CASE WHEN vd.vehicle_id IS NULL THEN pl.quantity * pl.unit_price ELSE 0 END) AS parts_stock_balance,
            SUM(CASE WHEN vd.vehicle_id IS NOT NULL THEN pl.quantity ELSE 0 END) AS vehicle_stock_quantity,
            SUM(CASE WHEN vd.vehicle_id IS NOT NULL THEN pl.quantity * pl.unit_price ELSE 0 END) AS vehicle_stock_balance
            FROM vehicle_details vd
            LEFT JOIN purchase_lines pl ON vd.id = pl.vehicle_detail_id
            WHERE vd.deleted_at IS NULL AND pl.deleted_at IS NULL");

        return response()->json(['status' => 'success', 'data' => $partsDetails]);
    }

    // public function stockSummary()
    // {
    //     $summary = DB::select("
    //         SELECT
    //             SUM(CASE WHEN vehicle_id IS NULL THEN total_quantity ELSE 0 END) AS parts_stock_quantity,
    //             SUM(CASE WHEN vehicle_id IS NULL THEN total_price ELSE 0 END) AS parts_stock_amount,

    //             SUM(CASE WHEN vehicle_id IS NOT NULL THEN total_quantity ELSE 0 END) AS vehicle_stock_quantity,
    //             SUM(CASE WHEN vehicle_id IS NOT NULL THEN total_price ELSE 0 END) AS vehicle_stock_amount
    //         FROM (
    //             SELECT
    //                 vd.id,
    //                 vd.vehicle_id,
    //                 CASE
    //                     WHEN vd.vehicle_id IS NOT NULL THEN CONCAT(v.name, ' - ', v.uid)
    //                     ELSE vd.car_name
    //                 END AS car_name,
    //                 vd.car_model,
    //                 SUM(pl.quantity) AS total_quantity,
    //                 SUM(pl.quantity * pl.unit_price) AS total_price
    //             FROM vehicle_details vd
    //             LEFT JOIN purchase_lines pl
    //                 ON vd.id = pl.vehicle_detail_id
    //                 AND pl.deleted_at IS NULL
    //             LEFT JOIN vehicles v
    //                 ON vd.vehicle_id = v.id
    //             WHERE vd.deleted_at IS NULL
    //             GROUP BY vd.id, vd.uid, vd.parts_name, car_name, vd.car_model
    //         ) AS t
    //     ");

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $summary
    //     ]);
    // }



    public function profitSummary(Request $request)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        // Get session values
        $reportDate = $request->gross_profit_report_date ?? 'today';
        $startDay   = $request->gross_profit_start_day;
        $endDay     = $request->gross_profit_end_day;

        // Base queries
        $expenseQuery = Expense::query();
        $saleQuery = SaleDetails::query(); // for scrap/parts breakdown

        // Apply filters based on report date
        if ($reportDate) {
            if ($reportDate === 'today') {
                $expenseQuery->whereDate('expense_date', today());
                $saleQuery->whereHas('sale', fn($q) => $q->whereDate('date', today()));
            } elseif ($reportDate === 'last_7_days') {
                $expenseQuery->whereBetween('expense_date', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
                $saleQuery->whereHas('sale', fn($q) => $q->whereBetween('date', [now()->subDays(6)->startOfDay(), now()->endOfDay()]));
            } elseif ($reportDate === 'this_month') {
                $expenseQuery->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year));
            } elseif ($reportDate === 'this_year') {
                $expenseQuery->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereYear('date', now()->year));
            } elseif ($reportDate === 'custom' && $startDay && $endDay) {
                $expenseQuery->whereBetween('expense_date', [$startDay, $endDay]);
                $saleQuery->whereHas('sale', fn($q) => $q->whereBetween('date', [$startDay, $endDay]));
            }
        }


        $expense = $expenseQuery
            ->selectRaw('SUM(amount) as totalExpense')
            ->first();

        // 🔹 Calculate scrap and parts sales
        $scrapSell = (clone $saleQuery)
            ->where('item_id', 0)
            ->sum('cost');

        $partsSell = (clone $saleQuery)
            ->where('item_id', '!=', 0)
            ->sum('cost');

        $purchaseAmount = (clone $saleQuery)
            ->selectRaw('SUM(item_actual_price * quantity) as totalPurchaseAmount')
            ->value('totalPurchaseAmount');


        $totalSell = $scrapSell + $partsSell;
        $totalExpense = $expense->totalExpense ?? 0;

        $grossProfit = $totalSell - $purchaseAmount;

        return response()->json([
            'status' => true,
            'scrapSell' => (float)$scrapSell,
            'partsSell' => (float)$partsSell,
            'totalSell' => (float)$totalSell,
            'purchaseAmount' => (float)$purchaseAmount,
            'totalExpense' => (float)$totalExpense,
            'grossProfit' => (float)$grossProfit,
        ]);
    }
}



