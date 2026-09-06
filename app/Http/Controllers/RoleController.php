<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name', 'desc')->get();
        return view('roles.index', compact('roles'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        if($role->name == 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be edited.');
        }

        $roles = Role::all();
        return view('roles.edit', compact('role', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function setPermissionsForm($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name', 'asc')->get();

        return view('roles.set_permissions', compact('role', 'permissions'));
    }

    public function storePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);


        return redirect()->back()->with('success', 'Permissions updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if(!$role) {
            return $res = [
                    'type' => 'error',
                    'title' => __('Error'),
                    'msg' => __('Role not found.')
                ];
        }

        if($role->name == 'Super Admin') {
            return $res = [
                    'type' => 'error',
                    'title' => __('Error'),
                    'msg' => __('Super Admin role cannot be deleted')
                ];
        }

        if(!$role->users()->exists()) {
            $role->delete();
            return $res = [
                    'type' => 'success',
                    'title' => __('Success'),
                    'msg' => __('Role deleted successfully.')
                ];

            } else {

                return $res = [
                    'type' => 'error',
                    'title' => __('Error'),
                    'msg' => __('Cannot delete role assigned to users.')
                ];
            }
    }
}
