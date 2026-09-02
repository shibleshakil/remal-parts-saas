<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleSaleProduct extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'vehicle_sale_id', 'product_id', 'product_name', 'unit_price',
        'quantity', 'cost', 'created_by', 'updated_by', 'deleted_by',
    ];
    public function product()
    {
        return $this->belongsTo(VehicleDetails::class, 'product_id');
    }
}
