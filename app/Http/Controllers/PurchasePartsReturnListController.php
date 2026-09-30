<?php

namespace App\Http\Controllers;

use App\Models\PurchasePartsReturnList;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchasePartsReturnListController extends Controller
{
    public function index(Request $request)
    {
        $invoiceNo = $request->input('invoice_no');
        $partsName = $request->input('parts_name');
        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d 00:00:00') : Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d 23:59:59') : Carbon::now()->startOfDay()->format('Y-m-d 23:59:59');

        $query = PurchasePartsReturnList::whereBetween('created_at', [$from, $to])->with(['purchaseLine', 'purchaseLine.vehicleDetail']);


        if ($invoiceNo) {
            $query->where(function ($q) use ($invoiceNo) {
                $q->where('invoice_no', 'like', "%{$invoiceNo}%")
                    ->orWhereHas('purchaseLine', function ($pl) use ($invoiceNo) {
                        $pl->where('invoice_no', 'like', "%{$invoiceNo}%");
                    });
            });
        }
        if ($partsName) {
            // Wrap in a group so this OR does not swallow the date/invoice filters.
            $query->where(function ($q) use ($partsName) {
                $q->where('item_name', 'like', "%{$partsName}%")
                    ->orWhereHas('purchaseLine.vehicleDetail', function ($vd) use ($partsName) {
                        $vd->where('parts_name', 'like', "%{$partsName}%");
                    });
            });
        }

        // Session pagination logic
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);
        $partsReturnLists = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();

        $sl = ($partsReturnLists->currentPage() - 1) * $perPage;

        $totalQuantity = (clone $query)->sum('quantity');
        $totalReturnAmount = (clone $query)->sum(\DB::raw('quantity * unit_price'));

        return view('purchase.parts_return_list', compact('partsReturnLists', 'sl', 'totalQuantity', 'totalReturnAmount'));
    }
}
