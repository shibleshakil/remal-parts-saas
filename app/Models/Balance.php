<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable=['balance', 'branch_id'];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }
}
