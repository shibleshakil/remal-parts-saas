<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'title', 'expense_type_id', 'expense_date','amount', 'note'
    ];
    public function expense_type()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

}
