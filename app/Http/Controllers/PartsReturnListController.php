<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PartsReturnList;
use Illuminate\Support\Facades\DB;

class PartsReturnListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $invoiceNo = $request->input('invoice_no');
        $partsName = $request->input('parts_name');
        $from = request('from') ? Carbon::parse(request('from'))->format('Y-m-d 00:00:00') : Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00');
        $to = request('to') ? Carbon::parse(request('to'))->format('Y-m-d 23:59:59') : Carbon::now()->startOfDay()->format('Y-m-d 23:59:59');

        $query = PartsReturnList::whereBetween('created_at', [$from, $to])->with(['vehicleDetail','withTrashedSale']);


        if ($invoiceNo) {
            $query->whereHas('withTrashedSale', function ($q) use ($invoiceNo) {
                $q->where('invoice_no', 'like', "%{$invoiceNo}%");
            });
        }
        if ($partsName) {
            $query->where('item_name', 'like', "%{$partsName}%")
            ->orWhereHas('vehicleDetail', function ($q) use ($partsName) {
                $q->where('parts_name', 'like', "%{$partsName}%");
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


        return view('sale.parts_return_list', compact('partsReturnLists', 'sl'));
    }
}
