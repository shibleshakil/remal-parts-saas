<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetails extends Model
{
    use HasFactory, BelongsToBranch;
    protected $fillable = [
        'sale_id', 'item_id','vehicle_id', 'item_type', 'item_name', 'branch_id',
        'unit_price', 'quantity', 'cost','purchase_line_id','item_actual_price'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

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
