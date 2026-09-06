<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;


if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin()
    {
       return Auth::user()->role->name == 'Super Admin' ?? 'false';

    }
}


if (!function_exists('branches')) {
    function branches()
    {
       return Branch::orderBy('name', 'asc')->get();
    }
}


if (!function_exists('active_branch_id')) {
    function active_branch_id()
    {
        return session('active_branch_id') ?? auth()->user()->branch_id;
    }
}
