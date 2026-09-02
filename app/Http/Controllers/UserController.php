<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('is_active', 1)->orderBy('name', 'asc')->get();
        return view('user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }
        
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $userData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|in:admin,employee'
        ]);

        if (!empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        }

        User::create($userData);

        return redirect()->route('user.index')->with('success' , 'User added succesfully');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $user = User::findOrFail($id);

        $userData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'nullable|string',
            'role' => 'required|in:admin,employee'
        ]);

        if (!empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        }
        $user->update($userData);

        return redirect()->route('user.index')->with('success' , 'User updated succesfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {

        if(!(\App\Helpers\CommonHelper::userHasAccess())){
            if(request()->ajax()){
                return response()->json(['message' => 'Sorry, you’re not authorized to access this section.'], 403);
            }

            return back()->with('error', 'Sorry, you’re not authorized to access this section.');
        }

        $user = User::findOrFail($id);
        $user->update(['is_active'=> 0]);
        return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('User deleted successfully!'),
            ]);
    }
}
