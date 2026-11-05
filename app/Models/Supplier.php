<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory,SoftDeletes;
        protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'created_by',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'supplier_id', 'id');
    }
}
