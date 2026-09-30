<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;


if (!function_exists('isSuperAdmin')) {
    /**
     * Check if the authenticated user has the Super Admin role.
     * Safe for users without a role and for unauthenticated contexts.
     */
    function isSuperAdmin()
    {
        $user = Auth::user();

        return $user !== null && $user->role?->name === 'Super Admin';
    }
}


if (!function_exists('branches')) {
    function branches()
    {
       return Branch::orderBy('name', 'asc')->get();
    }
}

if (!function_exists('active_branch_id')) {
    /**
     * Resolve the active branch for the current request.
     *
     * Order: session value -> user's home branch. Returns null when neither
     * exists (caller must decide how to handle that, BranchScope fails-loud).
     */
    function active_branch_id()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        $branchId = session('active_branch_id');

        // Non-super-admins are always pinned to their own branch: a stale
        // session value (e.g. left over from a previous login) is ignored.
        if (!$branchId || !isSuperAdmin()) {
            $branchId = $user->branch_id;
            session()->put('active_branch_id', $branchId);
        }

        return $branchId;
    }
}
