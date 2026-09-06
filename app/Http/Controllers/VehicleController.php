<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Balance;
use App\Models\Vehicle;
use App\Models\Supplier;
use App\Models\SaleDetails;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PurchaseLines;
use App\Models\VehicleDetails;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    public function index(Request $request)
    {

        $branchId = active_branch_id();

        $query = Vehicle::with([
            'sales',
            'details.saleDetails',
            'details.purchaseLines' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }
        ])
        ->whereHas('details.purchaseLines', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });

        // সার্চ অপশন
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('uid',  $search)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('chasis_no', 'like', "%{$search}%")
                    ->orWhereHas('details', function ($qq) use ($search) {
                        $qq->where('parts_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $query->where(function ($q) use ($from_date, $to_date) {
                $q->whereBetween('date', [$from_date, $to_date]);
            });
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->filled('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $vehicles = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();

        $vehicles->each(function ($vehicle) {
            $vehicle->details->transform(function ($part) {
                $sold_quantity = $part->saleDetails->sum('quantity');
                $available_quantity = $part->parts_quantity ?? 0;
                $total_quantity = $sold_quantity + $available_quantity;

                $part->sold_quantity = $sold_quantity;
                $part->available_quantity = $available_quantity;
                $part->total_quantity = $total_quantity;

                return $part;
            });
        });

        $sl = ($vehicles->currentPage() - 1) * $perPage;

        return view('vehicle.index', ['datas' => $vehicles, 'sl' => $sl]);
    }

    public function invoice($invoice_no)
    {
        $vehicle = Vehicle::with(['details', 'details.purchaseLines', 'purchaseTransaction'])
            ->where('invoice_no', $invoice_no)->firstorFail();

        return view('vehicle.invoice', compact('vehicle'));
    }


    public function create()
    {
        $vehicles = Vehicle::pluck('name')->unique();
        $parts = VehicleDetails::pluck('parts_name')->unique();
        $suppliers = Supplier::select('id', 'name')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        return view('vehicle.create', compact('vehicles', 'parts', 'suppliers', 'banks'));
    }

    public function store(Request $request)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );


        if ($request->paid_amount > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => [
                        'paid_amount' => ['Balance exceeds, please check available balance.']
                    ]
                ], 403);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }


        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'chasis_no' => 'required|string|max:255|unique:vehicles,chasis_no',
            'engine_no' => 'required|string|max:255|unique:vehicles,engine_no',
            'date' => 'required|date',
            'total_purchase_price' => 'required|numeric|min:0',
            'total_vehicle_price' => 'required|numeric|min:0',
            'product_name.*' => 'required|string|max:255',
            'quantity.*' => 'required|integer|min:1',
            'price.*' => 'required|numeric|min:0',
            'vehicle_price.*' => 'required|numeric|min:0',
            'vehicle_target_sell_price' => 'nullable|string|max:255',
            'purchase_date.*' => 'nullable|date',
            'target_sell_price.*' => 'nullable|string | max:100',
            'note' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'bank_id'  => 'nullable|exists:banks,id',
            'paid_amount' => 'nullable',
            'payment_method' => ['nullable', 'required_with:paid_amount', 'string', Rule::in(['cash', 'bank_transfer', 'cheque', 'due'])],
        ]);

        try {
            DB::beginTransaction();


            $year = date('Y');
            $count = PurchaseLines::withTrashed()
                ->whereYear('purchase_date', $year)
                ->count() + 1;

            $invoiceNo = 'VP' . $year . $count . '-BR' . active_branch_id();


            $vehicle = Vehicle::create([
                'name' => $validatedData['name'],
                'model' => $validatedData['model'],
                'chasis_no' => $validatedData['chasis_no'],
                'engine_no' => $validatedData['engine_no'],
                'date' => $validatedData['date'],
                'total_purchase_price' => $validatedData['total_purchase_price'],
                'total_vehicle_price' => $validatedData['total_vehicle_price'],
                'target_sell_price' => $validatedData['vehicle_target_sell_price'],
                'note' => $validatedData['note'] ?? null,
                'supplier_id' => $validatedData['supplier_id'],
                'invoice_no' => $invoiceNo
            ]);

            $balance->decrement('balance', $validatedData['paid_amount'] ?? 0 );
            $balance->refresh();

            $transaction = Transaction::create([
                'trxid' => 'VEH-' . uniqid(),
                'type' => 'vehicle_purchase',
                'transaction_date' => $vehicle->date,
                'final_amount' => $vehicle->total_purchase_price,
                'status' => $validatedData['paid_amount'] == 0 ? 'unpaid' : ($validatedData['paid_amount'] < $vehicle->total_purchase_price ? 'partial' : 'paid'),
                'note' => sprintf('Vehicle purchase: %s - %s', $vehicle->name, $vehicle->model),
                'created_by' => auth()->id(),
                'supplier_id' => $validatedData['supplier_id'],
                'vehicle_id' => $vehicle->id,
                'paid_amount'  => $validatedData['paid_amount'],
                'payment_method'  => $validatedData['payment_method'],
                'bank_id'  => $validatedData['bank_id'],
                'current_balance' => $balance->balance,
                'purchase_invoice_no' => $invoiceNo,
                'branch_id' => active_branch_id(),
            ]);

            $vehicle->update([
                'transaction_id' => $transaction->id
            ]);



            foreach ($validatedData['product_name'] as $index => $productName) {
                $detail = VehicleDetails::create([
                    'vehicle_id' => $vehicle->id,
                    'parts_name' => $productName,
                    'parts_quantity' => 0,
                    'car_name' => $validatedData['name'],
                    'car_model' => $validatedData['model'],
                    'unit_price' => 0,
                    'total_parts_price' => 0,
                    'date' => $validatedData['purchase_date'][$index] ?? $validatedData['date'],
                    'note' => $validatedData['note'] ?? null,
                    'branch_id' => active_branch_id(),
                ]);

                PurchaseLines::create([
                    'vehicle_detail_id' => $detail->id,
                    'quantity' => $validatedData['quantity'][$index],
                    'unit_price' => $validatedData['price'][$index],
                    'target_sell_price' => $validatedData['target_sell_price'][$index] ?? null,
                    'purchase_date' => $validatedData['purchase_date'][$index] ?? $validatedData['date'],
                    'supplier_id' => $validatedData['supplier_id'],
                    'invoice_no' => $invoiceNo,
                    'branch_id' => active_branch_id(),
                    'transaction_id' => $transaction->id,
                ]);
            }

            DB::commit();

            return redirect()->route('vehicle.index')
                ->with('success', 'Vehicle added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle store error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->with('error', 'An error occurred while saving the vehicle: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $vehicle = Vehicle::with('details', 'purchaseTransaction')->findOrFail($id);
        $vehicles = Vehicle::pluck('name')->unique();
        $parts = VehicleDetails::pluck('parts_name')->unique();
        $suppliers = Supplier::select('id', 'name')->get();
        $banks = Bank::select('id', 'bank_name', 'account_name', 'account_no')->latest()->get();

        return view('vehicle.edit', compact('vehicle', 'vehicles', 'banks', 'parts', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        if (($request->paid_amount - $vehicle->purchaseTransaction->paid_amount) > $balance->balance) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => [
                        'paid_amount' => ['Balance exceeds, please check available balance.']
                    ]
                ], 403);
            }

            return back()->with('error', 'Balance exceeds, please check available balance.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'chasis_no' => 'required|string|max:255|unique:vehicles,chasis_no,' . $id,
            'engine_no' => 'required|string|max:255|unique:vehicles,engine_no,' . $id,
            'date' => 'required|date',
            'total_purchase_price' => 'required|numeric|min:0',
            'total_vehicle_price' => 'required|numeric|min:0',
            'product_name.*' => 'required|string|max:255',
            'quantity.*' => 'required|integer|min:1',
            'price.*' => 'required|numeric|min:0',
            'vehicle_price.*' => 'required|numeric|min:0',
            'purchase_date.*' => 'nullable|date',
            'note' => 'nullable|string',
            'vehicle_target_sell_price' => 'nullable|string|max:255',
            'target_sell_price.*' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'bank_id'  => 'nullable|exists:banks,id',
            'paid_amount' => 'nullable',
            'payment_method' => ['nullable','required_with:paid_amount', 'string', Rule::in(['cash', 'bank_transfer', 'cheque','due'])],
        ]);

        try {
            DB::beginTransaction();

            $vehicle->update([
                'name' => $validatedData['name'],
                'model' => $validatedData['model'],
                'chasis_no' => $validatedData['chasis_no'],
                'engine_no' => $validatedData['engine_no'],
                'date' => $validatedData['date'],
                'total_purchase_price' => $validatedData['total_purchase_price'],
                'total_vehicle_price' => $validatedData['total_vehicle_price'],
                'target_sell_price' => $validatedData['vehicle_target_sell_price'],
                'note' => $validatedData['note'] ?? null,
                'supplier_id' => $validatedData['supplier_id'],
            ]);

            // Updating transection
            $transaction = Transaction::find($vehicle->transaction_id);

            $oldPaidAmount = $transaction ? $transaction->paid_amount : 0;
            $newPaidAmount = $validatedData['paid_amount'] ?? 0;

            // Calculate the net change
            $balanceChange = $newPaidAmount - $oldPaidAmount;
            $balance->balance = $balance->balance - $balanceChange;
            $balance->save();

            $transaction->update([
                'trxid' => 'VEH-' . uniqid(),
                'type' => 'vehicle_purchase',
                'transaction_date' => $vehicle->date,
                'final_amount' => $vehicle->total_purchase_price,
                'status' => 'paid',
                'note' => 'Vehicle purchase: ' . $vehicle->name . ' - ' . $vehicle->model,
                'created_by' => auth()->id(),
                'supplier_id' => $validatedData['supplier_id'],
                'paid_amount'  => $validatedData['paid_amount'],
                'payment_method'  => $validatedData['payment_method'],
                'bank_id'  => $validatedData['bank_id'],
                'current_balance' => $balance->balance
            ]);

            // Delete old details and purchase lines
            $vehicle->load('details.purchaseLines');

            $vehicle->details->each(function ($detail) {
                $detail->purchaseLines()->delete(); // delete purchase lines
                $detail->delete(); // delete vehicle detail
            });

            // Re-insert new details and purchase lines
            foreach ($validatedData['product_name'] as $index => $productName) {
                $detail = VehicleDetails::create([
                    'vehicle_id' => $vehicle->id,
                    'parts_name' => $productName,
                    'parts_quantity' => $validatedData['quantity'][$index],
                    'car_name' => $validatedData['name'],
                    'car_model' => $validatedData['model'],
                    'unit_price' => $validatedData['price'][$index],
                    'total_parts_price' => $validatedData['quantity'][$index] * $validatedData['price'][$index],
                    'date' => $validatedData['purchase_date'][$index] ?? $validatedData['date'],
                    'note' => $validatedData['note'] ?? null,
                    'branch_id' => active_branch_id(),
                ]);

                PurchaseLines::create([
                    'vehicle_detail_id' => $detail->id,
                    'quantity' => $validatedData['quantity'][$index],
                    'unit_price' => $validatedData['price'][$index],
                    'purchase_date' => $validatedData['purchase_date'][$index] ?? $validatedData['date'],
                    'target_sell_price' => $validatedData['target_sell_price'][$index],
                    'supplier_id' => $validatedData['supplier_id'],
                    'invoice_no' => $vehicle->invoice_no,
                    'transaction_id' => $vehicle->transaction_id,
                    'branch_id' => active_branch_id(),
                ]);
            }


            DB::commit();

            // Return JSON for AJAX, redirect for normal
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Vehicle updated successfully',
                ], 200);
            }

            return redirect()->route('vehicle.index')->with('success', 'Vehicle updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Vehicle update error: ' . $th->getMessage(), [
                'exception' => $th,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'An error occurred while updating the vehicle.')->withInput();
        }
    }

    public function delete($id)
    {
        $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );

        $selledVehicle = SaleDetails::where('vehicle_id', $id)->get();

        if (!count($selledVehicle) == 0) {
            if (request()->ajax()) {
                return response()->json([
                    'title' => __('Denied!'),
                    'message' => __('Vehicle has sale record!'),
                    'type' => 'error'
                ], 403);
            }

            return back()->with('error', __('Vehicle has sale record!'));
        }

        try {
            DB::beginTransaction();

            $vehicle = Vehicle::findOrFail($id);

            // Refund the purchase price to balance if transaction exists
            if ($vehicle->purchaseTransaction) {
                $balance->balance += $vehicle->purchaseTransaction->paid_amount ?? 0;
                $balance->save();
            }

            foreach ($vehicle->details as $detail) {
                $detail->purchaseLines()->delete();
                $detail->delete();
            }

            Transaction::where('type', 'vehicle_purchase')
                ->where('note', 'Vehicle purchase: ' . $vehicle->name . ' - ' . $vehicle->model)
                ->delete();

            $vehicle->delete();

            DB::commit();

            return [
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Vehicle deleted successfully!'),
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Vehicle delete error: ' . $th->getMessage(), [
                'exception' => $th,
                'user_id' => auth()->id(),
            ]);

            return [
                'type' => 'error',
                'title' => __('Error'),
                'msg' => __('An error occurred while deleting the vehicle.'),
            ];
        }
    }

    public function printBarcode($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        if (!$vehicle->barcode) {
            return redirect()->back()->with('error', 'Barcode not found for this vehicle.');
        }

        return view('vehicle.barcode', compact('vehicle'));
    }

    public function printQRcode($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        if (!$vehicle->barcode) {
            return redirect()->back()->with('error', 'QRcode not found for this vehicle.');
        }

        return view('vehicle.qrcode', compact('vehicle'));
    }
}
