<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('is_active', 1)
                ->where('users.branch_id', active_branch_id())
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->select('users.*', 'roles.name as role_name')
                ->orderBy('name', 'asc')
                ->get();

        return view('user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::pluck('name', 'id');
        $branches = Branch::pluck('name', 'id');
        return view('user.create', compact('roles','branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if (!empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        }

        $user = User::create($userData);

        $user->assignRole(Role::findById($request->role_id));

        return redirect()->route('user.index')->with('success' , 'User added succesfully');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::pluck('name', 'id');
        $branches = Branch::pluck('name', 'id');

        return view('user.edit', compact('user', 'roles', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $user = User::findOrFail($id);

        $userData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if (!empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        }else {
            $userData['password'] = $user->password;
        }
        
        $user->update($userData);

        // Sync role
        $role = Role::findById($request->role_id);
        $user->syncRoles([$role]);

        return redirect()->route('user.index')->with('success' , 'User updated succesfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {

        $user = User::findOrFail($id);
        $user->update(['is_active'=> 0]);
        return response()->json([
                'type' => 'success',
                'title' => __('Deleted!'),
                'msg' => __('User deleted successfully!'),
            ]);
    }
}
