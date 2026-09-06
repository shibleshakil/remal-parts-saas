<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Get active branch from session OR fallback to user's branch
            $activeBranchId = session('active_branch_id') ?? $user->branch_id;

            // Apply filter ALWAYS (even for super admin)
            if ($activeBranchId) {
                $builder->where($model->getTable() . '.branch_id', $activeBranchId);
            }
        }
    }
}
