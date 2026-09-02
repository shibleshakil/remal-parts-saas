<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use App\Models\VehicleDetails;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function stock(Request $request)
    {
        $type = $request->input('type', 'parts'); // all | parts | vehicle

        // 1. TYPE CONDITION
        switch ($type) {
            case 'parts':
                $typeCondition = "vd.vehicle_id IS NULL";
                break;

            case 'vehicle':
                $typeCondition = "vd.vehicle_id IS NOT NULL";
                break;

            default: // all
                $typeCondition = "1 = 1"; // no filter
                break;
        }

        // 2. ORDERING LOGIC
        $ordering = $type === 'vehicle'
            ? 'vd.car_name ASC'
            : 'total_quantity ASC';

        // 3. SELECT OPTION (car name formatting only for vehicles)
        $selectOpt = $type === 'vehicle'
            ? 'vd.id, vd.uid, vd.parts_name, concat(v.name, " - ", v.uid) as car_name, vd.car_model'
            : 'vd.id, vd.uid, vd.parts_name, vd.car_name, vd.car_model';


        // 4. BASE QUERY
        $sql = "SELECT
                    $selectOpt,
                    SUM(pl.unit_price * pl.quantity) as total_price,
                    SUM(pl.quantity) as total_quantity
                FROM vehicle_details vd
                LEFT JOIN purchase_lines pl ON vd.id = pl.vehicle_detail_id
                LEFT JOIN vehicles v ON vd.vehicle_id = v.id
                WHERE $typeCondition
                AND vd.deleted_at IS NULL
                AND (pl.deleted_at IS NULL OR pl.deleted_at IS NULL)";

        $params = [];

        // 5. FILTERS
        if ($request->filled('uid')) {
            $sql .= " AND vd.uid LIKE ?";
            $params[] = "%{$request->uid}%";
        }

        if ($request->filled('parts_name')) {
            $sql .= " AND vd.parts_name LIKE ?";
            $params[] = "%{$request->parts_name}%";
        }

        if ($request->filled('car_name')) {
            $sql .= " AND vd.car_name LIKE ?";
            $params[] = "%{$request->car_name}%";
        }

        if ($request->filled('car_model')) {
            $sql .= " AND vd.car_model LIKE ?";
            $params[] = "%{$request->car_model}%";
        }

        // 6. GROUP & ORDER
        $sql .= " GROUP BY vd.id, vd.uid, vd.parts_name, vd.car_name, vd.car_model
                ORDER BY $ordering";

        $stocks = DB::select($sql, $params);

        return view('reports.stock', ['datas' => $stocks, 'type' => $type]);
    }


    public function grossProfit(Request $request)
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        // Set the date range into session
        $this->setDateRange($request, 'gross_profit');

        // Get session values
        $reportDate = session('gross_profit_report_date') ?? 'today';
        $startDay   = session('gross_profit_start_day');
        $endDay     = session('gross_profit_end_day');
        $vehicleId  = session('gross_profit_vehicle_id');
        $partId     = session('gross_profit_part_id');

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
            } elseif ($reportDate === 'last_30_days') {
                $expenseQuery->whereBetween('expense_date', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
                $saleQuery->whereHas('sale', fn($q) => $q->whereBetween('date', [now()->subDays(29)->startOfDay(), now()->endOfDay()]));
            }elseif ($reportDate === 'last_month') {
                $lastMonth = now()->subMonth();

                $expenseQuery->whereMonth('expense_date', $lastMonth->month)
                            ->whereYear('expense_date', $lastMonth->year);

                $saleQuery->whereHas('sale', function ($q) use ($lastMonth) {
                    $q->whereMonth('date', $lastMonth->month)
                    ->whereYear('date', $lastMonth->year);
                });
            }
             elseif ($reportDate === 'this_month') {
                $expenseQuery->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year));
            } elseif ($reportDate === 'this_year') {
                $expenseQuery->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereYear('date', now()->year));
            } elseif ($reportDate === 'custom' && $startDay && $endDay) {
                $expenseQuery->whereBetween('expense_date', [$startDay, $endDay]);
                $saleQuery->whereHas('sale', fn($q) => $q->whereBetween('date', [$startDay, $endDay]));
            }elseif ($reportDate == 'vehicle') {
                if ($vehicleId != 'all') {
                    $saleQuery->where('vehicle_id', $vehicleId);
                }

            }elseif ($reportDate == 'parts') {
                if ($partId != 'all') {
                    $saleQuery->where('item_id', $partId);
                }
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

        // Vehicles and Parts for dropdowns
        $vehicles = Vehicle::select('id', 'name', 'uid')->orderBy('name', 'ASC')->get();
        $parts = VehicleDetails::select('id', 'parts_name', 'uid')->orderBy('parts_name', 'ASC')->get();

        $totalSell = $scrapSell + $partsSell;
        $totalExpense = $expense->totalExpense ?? 0;

        return view('reports.gross_profit', compact(
            'scrapSell',
            'partsSell',
            'totalSell',
            'purchaseAmount',
            'totalExpense',
            'parts',
            'vehicles'
        ));
    }


    public function vehicleSale(Request $request)
    {
        $query = Vehicle::query();

        // Apply filters
        if ($request->filled('vehicle_id')) {
            $query->where('id', $request->vehicle_id);
        }

        // Get all sale details with vehicle details
        $vehicleSales = $query->with('saleDetails','saleDetails.partsDetail', 'saleDetails.sale')->get();

        $vehicles = Vehicle::select('id', 'name', 'uid')->orderBy('name', 'ASC')->get();

        return view('reports.vehicle_sale', compact('vehicleSales', 'vehicles'));
    }


    public function dueReport(Request $request)
    {
        $query = Sale::whereColumn('final_amount', '>', 'paid_amount');

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by invoice
        if ($request->filled('invoice')) {
            $query->where('invoice_no', $request->invoice);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $dueSales = $query->get();
        $customers = Customer::select('id', 'name')->latest()->get();

        return view('reports.due_report', compact('dueSales', 'customers'));
    }

    public function setDateRange($request, $from)
    {
        $reportDateKey   = $from . '_report_date';
        $startDayKey     = $from . '_start_day';
        $endDayKey       = $from . '_end_day';
        $vehicleId       = $from . '_vehicle_id';
        $partId          = $from . '_part_id';

        if ($request->has($reportDateKey)) {
            // Save report date (today, this_month, custome, etc.)
            session([$reportDateKey => $request->$reportDateKey]);

            if ($request->$reportDateKey === 'custom') {
                // Save start and end if provided
                if ($request->filled($startDayKey)) {
                    session([$startDayKey => $request->$startDayKey]);
                }

                if ($request->filled($endDayKey)) {
                    session([$endDayKey => $request->$endDayKey]);
                }
            } else {
                // Reset start & end days if not custom
                session([$startDayKey => null, $endDayKey => null]);
            }

            if ($request->$reportDateKey === 'vehicle') {
                // Save start and end if provided
                if ($request->filled($vehicleId)) {
                    session([$vehicleId => $request->$vehicleId]);
                }
            } else {
                // Reset start & end days if not custom
                session([$vehicleId => null]);
            }

            if ($request->$reportDateKey === 'parts') {
                // Save start and end if provided
                if ($request->filled($partId)) {
                    session([$partId => $request->$partId]);
                }
            } else {
                // Reset start & end days if not custom
                session([$partId => null]);
            }
        }else{
            session()->forget([$startDayKey, $endDayKey, $reportDateKey, $vehicleId,  $partId]);
        }


        if ($request->has('data_reset')) {
            session()->forget([$startDayKey, $endDayKey, $reportDateKey, $vehicleId,  $partId]);
            session([$reportDateKey => 'today']);
        }
    }

    public function getSaleDetails(Request $request)
    {
        $reportDate = $request->report_date;
        $vehicleId = $request->vehicle_id;
        $partId = $request->part_id;
        $startDay = $request->start_day;
        $endDay = $request->end_day;
        $dataFor = $request->data_for;

       // Get session values
        $reportDate = session('gross_profit_report_date') ?? 'today';
        $startDay   = session('gross_profit_start_day');
        $endDay     = session('gross_profit_end_day');
        $vehicleId  = session('gross_profit_vehicle_id');
        $partId     = session('gross_profit_part_id');

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
            }elseif($reportDate === 'last_30_days'){
                $expenseQuery->whereBetween('expense_date', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
                $saleQuery->whereHas('sale', fn($q) => $q->whereBetween('date', [now()->subDays(29)->startOfDay(), now()->endOfDay()]));
            }elseif ($reportDate === 'last_month') {
                $expenseQuery->whereMonth('expense_date', now()->month - 1)->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereMonth('date', now()->month - 1)->whereYear('date', now()->year));
            }elseif ($reportDate === 'this_month') {
                $expenseQuery->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year));
            } elseif ($reportDate === 'this_year') {
                $expenseQuery->whereYear('expense_date', now()->year);
                $saleQuery->whereHas('sale', fn($q) => $q->whereYear('date', now()->year));
            } elseif ($reportDate === 'custom' && $startDay && $endDay) {
                $expenseQuery->whereBetween('expense_date', [$startDay, $endDay]);
                $saleQuery->whereHas('sale', fn($q) => $q->whereBetween('date', [$startDay, $endDay]));
            }elseif ($reportDate == 'vehicle') {
                if ($vehicleId != 'all') {
                    $saleQuery->where('vehicle_id', $vehicleId);
                }

            }elseif ($reportDate == 'parts') {
                if ($partId != 'all') {
                    $saleQuery->where('item_id', $partId);
                }
            }
        }

        if ($dataFor === 'scrap') {
            $saleQuery->where('item_id', 0);
        } elseif ($dataFor === 'parts') {
            $saleQuery->where('item_id', '!=', 0);
        }

        $datas = $saleQuery
            ->with('sale', 'partsDetail')
            ->get();

        return response()->json($datas);
    }
}
