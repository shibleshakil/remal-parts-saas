<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseTypeController extends Controller
{
    public function index(Request $request){
        $query = ExpenseType::query();

        // সার্চ অপশন
        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->Where('name', 'like', "%{$search}%");
            });
        }

        // যদি নতুন করে per_page পাঠায়, তাহলে সেটাকে সেশন-এ রাখবো
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }

        // সেশন থেকে per_page নেবো, না থাকলে ডিফল্ট 10
        $perPage = session('per_page', 10);

        $expenceTypes = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();


        $sl = ($expenceTypes->currentPage() - 1) * $perPage;

        return view('expenseType.index', ['datas' => $expenceTypes,'sl' => $sl]);
    }


    public function create(){
        return view('expenseType.create');
    }


    public function store(Request $request){
        $request->validate([
            'name' => ['required', 'unique:expense_types,name,','max:255'],
        ]);

        DB::beginTransaction();

        try {
            $data = new ExpenseType();
            $data->name = $request->name;
            $data->created_by = auth()->user()->id;
            $data->save();

            DB::commit();
            return redirect()->route('expense-type.index')->with('success', 'Data added successfully');

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', 'An error occured');
        }

    }
    public function edit($id)
    {
        $data = ExpenseType::findorFail($id);
        return view('expenseType.edit', compact('data' ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required','max:255'],
        ]);

        DB::beginTransaction();

        try {
            $data = ExpenseType::find($request->id);
            $data->name = $request->name;
            $data->updated_by = auth()->user()->id;
            $data->save();

            DB::commit();
            return redirect()->route('expense-type.index')->with('success', 'Data updated successfully');

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return back()->with('error', 'An error occured');
        }

    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = ExpenseType::find($id);
            if (!$data) {
                DB::rollback();
                return $res = [
                    'type'=> 'error',
                    'title'=> __('Error'),
                    'msg'=> __('Data not found!')
                ];
            }

            $data->deleted_by = auth()->user()->id;
            $data->save();
            $data->delete();

            DB::commit();

            return $res = [
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Data deleted successfully!'),
            ];

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return $res = [
                'type'=> 'error',
                'title'=> __('Error'),
                'msg'=> __('An Error Occured! Try Again')
            ];
        }

    }
}
