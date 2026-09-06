<?php

namespace App\Http\Controllers;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bank::query();

        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->Where('bank_name', 'like', "%{$search}%")
                ->orWhere('account_name', 'like', "%{$search}%")
                ->orWhere('account_no', 'like', "%{$search}%")
                ->orWhere('branch', 'like', "%{$search}%");
            });
        }


        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }


        $perPage = session('per_page', 10);

        $banks = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();


        $sl = ($banks->currentPage() - 1) * $perPage;

        return view('banks.index', ['sl' => $sl, 'datas' => $banks]);

    }

    public function trash(Request $request)
    {
        $query = Bank::onlyTrashed();

        if ($request->filled('search')) {
           $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->Where('bank_name', 'like', "%{$search}%")
                ->orWhere('account_name', 'like', "%{$search}%")
                ->orWhere('account_no', 'like', "%{$search}%")
                ->orWhere('branch', 'like', "%{$search}%");
            });
        }
        if ($request->has('per_page')) {
            session(['per_page' => $request->per_page]);
        }
        $perPage = session('per_page', 10);
        $banks = $query->orderBy('id', 'DESC')->paginate($perPage)->withQueryString();
        $sl = ($banks->currentPage() - 1) * $perPage;
        return view('banks.trash', ['sl' => $sl, 'datas' => $banks]);


    }

        public function restore($id)
        {
            $bank = Bank::onlyTrashed()->findOrFail($id);
            $bank->restore();

            return response()->json([
                'type' => 'success',
                'title' => __('Restored!'),
                'msg' => __('Bank restored successfully!'),
            ]);

        }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('banks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data  = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255|unique:banks,account_no',
            'branch' => 'nullable|string|max:255',
        ]);


        Bank::create($data);

        return redirect()->route('banks.index')->with('success', 'Bank created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bank = Bank::findOrFail($id);
        return view('banks.edit', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data  = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255|unique:banks,account_no,'.$id,
            'branch' => 'nullable|string|max:255',
        ]);
        $bank = Bank::findOrFail($id);
        $bank->update($data);
        return redirect()->route('banks.index')->with('success', 'Bank updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bank = Bank::findOrFail($id);
        $bank->delete();
        return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('Bank deleted successfully!'),
            ]);
    }
}
