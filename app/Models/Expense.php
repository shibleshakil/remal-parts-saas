<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory,SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'title', 'expense_type_id', 'expense_date','amount', 'note', 'created_by', 'branch_id'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    public function expense_type()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

}
