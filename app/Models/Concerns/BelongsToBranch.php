<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Automatically stamps the active branch on new records.
 *
 * Any model using BranchScope must never rely on the database default for
 * branch_id (which silently sends records to branch 1). This trait fills
 * branch_id from the session/user context and fails loud when it cannot.
 */
trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::creating(function (Model $model) {
            if (!isset($model->branch_id) || $model->branch_id === null || $model->branch_id === '') {
                $branchId = active_branch_id();

                if (!$branchId && !isSuperAdmin()) {
                    abort(403, 'No branch assigned to your account. Please contact the administrator.');
                }

                $model->branch_id = $branchId;
            }
        });
    }
}
