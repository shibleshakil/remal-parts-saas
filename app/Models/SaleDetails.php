<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'sale_id', 'item_id','vehicle_id', 'item_type', 'item_name',
        'unit_price', 'quantity', 'cost','purchase_line_id','item_actual_price'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function partsDetail()
    {
        return $this->belongsTo(VehicleDetails::class, 'item_id');
    }

    public function purchaseLines()
    {
        return $this->belongsTo(PurchaseLines::class, 'purchase_line_id');
    }
}
