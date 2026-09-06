<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable=['balance', 'branch_id'];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }
}
