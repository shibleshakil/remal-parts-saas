<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseType extends Model
{
    use HasFactory,SoftDeletes;

    public function expenses(){
        return $this->hasMany(Expense::class);
    }

}
