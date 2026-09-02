<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Balance;
use App\Models\Supplier;
use App\Models\SaleDetails;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PurchaseLines;
use App\Models\VehicleDetails;
use Illuminate\Validation\Rule;
use App\Exports\SparePartsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SparePartsController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleDetails::whereNull('vehicle_id')->with(['purchaseLines']);

        // সার্চ অপশন
        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('uid', $search)
                ->orWhere('parts_name', 'like', "%{$search}%")
                ->orWhere('car_name', 'like', "%{$search}%")
                ->orWhere('car_model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {

            $from_date = Carbon::parse($request->from_date)->startOfDay();
            $to_date   = Carbon::parse($request->to_date)->endOfDay();

            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $parts = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();

        $sl = ($parts->currentPage() - 1) * $perPage;

        return view('spare-parts.index', ['datas' => $parts, 'sl' => $sl]);
    }

    public function invoiceList(Request $request){
        $query = PurchaseLines::query();
        $query->whereHas('vehicleDetail', function ($q) {
            $q->whereNull('vehicle_id');
        })
        ->with(['vehicleDetail'])
        ->whereNotNull('invoice_no');

        // সার্চ অপশন
        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%");
            });
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $parts = $query->orderBy('invoice_no', 'DESC')->paginate($perPage)->withQueryString();

        return view('spare-parts.invoice_list', ['datas' => $parts]);
    }

    public function invoice($invoice_no){
        $data = PurchaseLines::where('invoice_no', $invoice_no)
        ->with(['vehicleDetail', 'transaction'])
        ->firstorFail();

        return view('spare-parts.invoice', ['invoice' => $data]);
    }

    public function searchParts(Request $request)
    {
        $query = VehicleDetails::with(['vehicle']);

        // সার্চ অপশন
        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->Where('parts_name', 'like', "%{$search}%")
                ->orWhere('uid',  $search);
            });
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $parts = $query->orderBy('id', 'DESC')
        ->paginate($perPage)
        ->withQueryString();

        $sl = ($parts->currentPage() - 1) * $perPage;

        return view('spare-parts.search_products', ['datas' => $parts, 'sl' => $sl]);
    }

    public function print(Request $request)
    {
        $query = VehicleDetails::whereNull('vehicle_id')->with(['saleDetails', 'purchaseLines']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('parts_name', 'like', "%{$search}%")
                    ->orWhere('car_name', 'like', "%{$search}%")
                    ->orWhere('car_model', 'like', "%{$search}%")
                    ->orWhere('uid', $search);
            });
        }

        $datas = $query->orderBy('id', 'DESC')->get();

        $title = 'Spare Parts List';

        return view('spare-parts.print', compact('datas', 'title'));
    }
    public function export(Request $request)
    {
        $search = $request->search ?? null;

        // For very large datasets, you can queue the export
        // return Excel::queue(new SparePartsExport($search), 'spare_parts.xlsx');

        // Normal download
        return Excel::download(new SparePartsExport($search), 'spare_parts.xlsx');
    }

    public function searchPartsPrint(Request $request)
    {
        $query = VehicleDetails::with(['vehicle']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->Where('parts_name', 'like', "%{$search}%")
                    ->orWhere('uid',  $search);
            });
        }

        $datas = $query->orderBy('id', 'DESC')->get();

        $title ='Product List';

        return view('spare-parts.print', compact('datas', 'title'));
    }
    public function searchPartsExport(Request $request)
    {
        $search = $request->search ?? null;
        $vehicleSearch = true;

        // Normal download
        return Excel::download(new SparePartsExport($search, $vehicleSearch), 'products.xlsx');
    }

    public function create()
    {
        $carNames = VehicleDetails::pluck('car_name')->unique();
        $suppliers = Supplier::select('id','name')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();


        return view('spare-parts.create', compact('carNames','suppliers','banks'));
    }

    public function store(Request $request)
    {
        $balance = Balance::firstOrCreate([], ['balance' => 0]);

        if ($request->paid_amount > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => 'Balance exceeds, please check available balance.'
                ], 422);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }

        $validated = $request->validate([
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'parts_name'         => 'required|string|max:255',
            'car_name'           => 'required|string|max:255',
            'car_model'          => 'required|string|max:255',
            'unit_price'         => 'required|numeric|min:0',
            'parts_quantity'     => 'required|integer|min:1',
            'total_parts_price'  => 'required|numeric|min:0',
            'target_sell_price'  => 'nullable|string|max:255',
            'date'               => 'required|date',
            'note'               => 'nullable|string|max:1000',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'payment_method' => [
                                    'nullable',
                                    'required_with:paid_amount',
                                    'string',
                                    Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])
                                ],
            'bank_id'            => ['nullable'],
            'paid_amount'        => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $sparePart = VehicleDetails::create([
                'vehicle_id'        => null,
                'parts_name'        => $validated['parts_name'],
                'car_name'          => $validated['car_name'],
                'car_model'         => $validated['car_model'],
                'unit_price'        => 0,
                'parts_quantity'    => 0,
                'total_parts_price' => 0,
                'target_sell_price' => $validated['target_sell_price'] ?? null,
                'date'              => $validated['date'],
                'note'              => $validated['note'] ?? null,
            ]);

            // update Balance
            $balance->decrement('balance', $validated['paid_amount']??0);
            $balance->refresh();

            if ($validated['parts_name']) {
                $year = date('Y');
                $count = PurchaseLines::withTrashed()
                    ->whereYear('purchase_date', $year)
                    ->count() + 1;

                $invoiceNo = 'SP' . $year . $count;
            }

            $transaction = Transaction::create([
                'trxid'            => 'SP-' . uniqid(),
                'type'             => 'spare_part_purchase',
                'transaction_date' => $validated['date'],
                'final_amount'     => round($validated['total_parts_price'], 2),
                'status'           => $validated['total_parts_price'] > $validated['paid_amount'] ? 'partially_paid' : 'paid',
                'note'             => $validated['note']??null,
                'created_by'       => auth()->id() ?? null,
                'supplier_id'      => $validated['supplier_id'],
                'payment_method'   => $validated['payment_method'],
                'bank_id'          => $validated['bank_id'],
                'paid_amount'      => $validated['paid_amount'],
                'current_balance'  => $balance->balance,
                'vehicle_details_id' => $sparePart->id,
                'purchase_invoice_no' => $invoiceNo,
            ]);

            PurchaseLines::create([
                'vehicle_detail_id' => $sparePart->id,
                'transaction_id'    => $transaction->id,
                'target_sell_price' => $validated['target_sell_price'] ?? null,
                'quantity'          => $validated['parts_quantity'],
                'unit_price'        => round($validated['unit_price'], 2),
                // 'purchase_date'     => $validated['date'],
                'supplier_id'       => $validated['supplier_id'],
                'invoice_no'       => $invoiceNo,
            ]);


            DB::commit();

            return redirect()->route('spare-parts.index')->with('success', 'Spare part and transaction created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error storing spare part', [
                'message' => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
                'input'   => $validated,
            ]);

            return back()->withInput()->with('error', 'An error occurred while saving the spare part.');
        }
    }


    public function ajaxStore(Request $request)
    {
        $validated = $request->validate([
            'parts_name'         => 'required|string|max:255',
            'car_name'           => 'required|string|max:255',
            'car_model'          => 'required|string|max:255',
        ]);



        $sparePart = VehicleDetails::create([
            'vehicle_id'        => null,
            'parts_name'        => $validated['parts_name'],
            'car_name'          => $validated['car_name'],
            'car_model'         => $validated['car_model'],
            'unit_price'        => 0,
            'parts_quantity'    => 0,
            'total_parts_price' => 0,
        ]);



        return response()->json([
            'status' => 'success',
            'part'   => $sparePart,
        ]);
    }

    public function edit($id)
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $spareParts = VehicleDetails::with('purchaseLines', 'purchaseLines.transaction')->findOrFail($id);
        $carNames = VehicleDetails::pluck('car_name')->unique();
        $suppliers = Supplier::select('id','name')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        return view('spare-parts.edit', compact('spareParts', 'carNames','suppliers','banks'));
    }

    public function update(Request $request, $id)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $balance = Balance::firstOrCreate([], ['balance' => 0]);

        $totalPaid = array_sum($request->paid_amount ?? []);
        $totalPrevPaid = array_sum($request->old_paid_amount ?? []);


        if ($totalPaid - $totalPrevPaid  > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => 'Balance exceeds, please check available balance.'
                ], 422);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }


        $validated = $request->validate([
            'parts_name'          => 'required|string|max:255',
            'car_name'            => 'required|string|max:255',
            'car_model'           => 'required|string|max:255',
            'transaction_id.*'    => 'required|exists:transactions,id',
            'purchase_line_id.*'  => 'required|exists:purchase_lines,id',
            'unit_price.*'        => 'required|numeric|min:0',
            'parts_quantity.*'    => 'required|integer|min:1',
            'total_parts_price.*' => 'required|numeric|min:0',
            'target_sell_price.*' => 'nullable|string|max:255',
            'date.*'              => 'required|date',
            'supplier_id.*'       => 'nullable|exists:suppliers,id',
            'paid_amount.*'       => 'nullable|numeric|min:0',
            'payment_method.*'    => ['nullable','required_with:paid_amount','string', Rule::in(['cash', 'bank_transfer', 'cheque'])],
            'bank_id.*'           => 'nullable|exists:banks,id',
            'note'                => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $sparePart = VehicleDetails::findOrFail($id);
            $sparePart->update([
                'parts_name'        => $validated['parts_name'],
                'car_name'          => $validated['car_name'],
                'car_model'         => $validated['car_model'],
                'target_sell_price' => $validated['target_sell_price'][0]  ?? null,
                // 'date'              => $validated['date'],
                'note'              => $validated['note'] ?? null,
            ]);

            foreach ($validated['purchase_line_id'] as $index => $lineId) {
                // update transaction
                $transaction = Transaction::findOrFail($validated['transaction_id'][$index]);
                $oldPaidAmount = $transaction ? $transaction->paid_amount : 0;
                $newPaidAmount = $validated['paid_amount'][$index] ?? 0;

                // Calculate the net change
                $balanceChange = $newPaidAmount - $oldPaidAmount;
                $balance->balance = $balance->balance - $balanceChange;
                $balance->save();

                $transaction->update([
                    'final_amount'     => round($validated['total_parts_price'][$index], 2),
                    'transaction_date' => $validated['date'][$index],
                    'updated_by'       => auth()->id(),
                    'supplier_id'      => $validated['supplier_id'][$index],
                    'payment_method'   => $validated['payment_method'][$index],
                    'bank_id'          => $validated['bank_id'][$index] ?? null,
                    'paid_amount'      => $validated['paid_amount'][$index],
                    'status'           => $validated['total_parts_price'][$index] > $validated['paid_amount'][$index] ? 'partially_paid' : 'paid',
                    'current_balance' => $balance->balance,
                ]);
                // Update purchase line
                $purchaseLine = PurchaseLines::findOrFail($lineId);
                $purchaseLine->update([
                    'quantity'      => $validated['parts_quantity'][$index],
                    'unit_price'    => round($validated['unit_price'][$index], 2),
                    'purchase_date' => $validated['date'][$index],
                    'target_sell_price' => $validated['target_sell_price'][$index] ?? null,
                    'supplier_id'       => $validated['supplier_id'][$index],
                ]);


            }

            DB::commit();

            return redirect()->route('spare-parts.index')->with('success', 'Spare part and transaction updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating spare part', ['message' => $th->getMessage()]);
            return back()->with('error', 'An error occurred while updating the spare part.');
        }
    }

    public function delete($id)
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $balance = Balance::firstOrCreate([], ['balance' => 0]);
        $selledPart= SaleDetails::where('item_id', $id)->get();

        if (!count($selledPart) == 0) {
            if (request()->ajax()) {
                return response()->json([
                    'title' => __('Denied!'),
                    'message' => __('Part has sale record!'),
                    'type' => 'error'
                ], 403);
            }

            return back()->with('error', __('Part has sale record!'));
        }

        try {
            DB::beginTransaction();

            $sparePart = VehicleDetails::findOrFail($id);

            $totalPaidAmount = PurchaseLines::where('vehicle_detail_id', $sparePart->id)
                ->whereNull('purchase_lines.deleted_at')
                ->join('transactions', 'purchase_lines.transaction_id', '=', 'transactions.id')
                ->whereNull('transactions.deleted_at')
                ->sum('transactions.paid_amount');

            $balance->balance += $totalPaidAmount ?? 0;
            $balance->save();

            $transactionIds = $sparePart->purchaseLines()->pluck('transaction_id');

            // Delete transactions
            Transaction::whereIn('id', $transactionIds)->delete();

            // Delete purchase lines
            $sparePart->purchaseLines()->delete();

            $sparePart->delete();

            DB::commit();

            return response()->json([
                'type'  => 'success',
                'title' => 'Deleted!',
                'msg'   => 'Spare part and transaction deleted successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting spare part', ['message' => $th->getMessage()]);
            return response()->json([
                'type'  => 'error',
                'title' => 'Error',
                'msg'   => 'An error occurred while deleting the spare part.',
            ]);
        }
    }

    public function printBarcode($id)
    {
        $sparePart = VehicleDetails::findOrFail($id);

        if (!$sparePart->barcode) {
            return back()->with('error', 'Barcode not available for this spare part.');
        }

        return view('spare-parts.barcode', compact('sparePart'));
    }

    public function printQRcode($id)
    {
        $sparePart = VehicleDetails::findOrFail($id);

        if (!$sparePart->barcode) {
            return back()->with('error', 'Qr code not available for this spare part.');
        }

        return view('spare-parts.qr_code', compact('sparePart'));
    }

//    public function addSimilar($id)
//    {
//        $spareParts = VehicleDetails::findOrFail($id);
//        $suppliers = Supplier::select('id','name')->get();
//        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();
//
//        return view('spare-parts.add-similar', compact('spareParts','suppliers','banks'));
//    }
//
//    public function storeSimilarPurchase(Request $request, $id)
//    {
//        $balance = Balance::firstOrCreate([], ['balance' => 0]);
//
//        if ($request->paid_amount > $balance->balance) {
//            if ($request->ajax()) {
//                return response()->json([
//                    'errors' => 'Balance exceeds, please check available balance.'
//                ], 422);
//            }
//
//            return back()->with('error', 'Balance exceeds, please check available balance.');
//        }
//
//        $validated = $request->validate([
//            'unit_price'     => 'required|numeric|min:0',
//            'parts_quantity' => 'required|integer|min:1',
//            'date'           => 'required|date',
//            'supplier_id'    => 'nullable|exists:suppliers,id',
//            'paid_amount'    => 'required|numeric|min:1',
//            'payment_method' => ['nullable', 'string', Rule::in(['cash', 'bank_transfer', 'cheque'])],
//            'bank_id'        => 'nullable|exists:banks,id',
//            'target_sell_price' => 'nullable'
//        ]);
//
//        try {
//            DB::beginTransaction();
//
//            $sparePart = VehicleDetails::findOrFail($id);
//
//             $balance->decrement('balance', $validated['paid_amount']);
//             $balance->refresh();
//
//            $transaction = Transaction::create([
//                'trxid'            => 'SP-' . uniqid(),
//                'type'             => 'spare_part_purchase',
//                'transaction_date' => $validated['date'],
//                'final_amount'     => round($validated['unit_price'] * $validated['parts_quantity'], 2),
//                'status'           => round($validated['unit_price'] * $validated['parts_quantity'], 2) > $validated['paid_amount'] ? 'partially_paid' : 'paid',
//                'note'             => 'Additional purchase of: ' . $sparePart->parts_name . ' (' . $sparePart->car_name . ' - ' . $sparePart->car_model . ')',
//                'created_by'       => auth()->id() ?? null,
//                'supplier_id'      => $validated['supplier_id'],
//                'paid_amount'      => $validated['paid_amount'],
//                'payment_method'   => $validated['payment_method'],
//                'bank_id'          => $validated['bank_id'],
//                'current_balance'  => $balance->balance,
//            ]);
//
//            PurchaseLines::create([
//                'vehicle_detail_id' => $sparePart->id,
//                'transaction_id' => $transaction->id,
//                'quantity' => $validated['parts_quantity'],
//                'target_sell_price' => $validated['target_sell_price'] ?? null,
//                'unit_price' => round($validated['unit_price'], 2),
//                'purchase_date' => $validated['date'],
//                'supplier_id'      => $validated['supplier_id'],
//            ]);
//
//
//            DB::commit();
//
//            return redirect()->route('spare-parts.index')->with('success', 'Similar spare part purchase added successfully.');
//        } catch (\Throwable $th) {
//            DB::rollBack();
//            Log::error('Error adding similar purchase', [
//                'message' => $th->getMessage(),
//                'trace'   => $th->getTraceAsString(),
//            ]);
//
//            return back()->withInput()->with('error', 'An error occurred while adding the similar purchase.');
//        }
//    }
}
