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
     *
     * Rules:
     * - Authenticated user with an active branch -> strict filter on that branch.
     * - Super Admin with NO active branch -> intentionally unscoped (global view).
     * - Any other user with no resolvable branch -> fail-loud: the account is
     *   misconfigured (no branch assigned) and must not silently see all data.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::check()) {
            return;
        }

        $activeBranchId = active_branch_id();

        if ($activeBranchId) {
            $builder->where($model->getTable() . '.branch_id', $activeBranchId);

            return;
        }

        if (!isSuperAdmin()) {
            abort(403, 'No branch assigned to your account. Please contact the administrator.');
        }
    }
}
