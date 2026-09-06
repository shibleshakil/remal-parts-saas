<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsReturnList extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_detail_id',
        'sale_id',
        'quantity',
        'item_name',
        'unit_price',
        'refund_amount',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    // Relationships can be defined here if needed
    public function vehicleDetail()
    {
        return $this->belongsTo(VehicleDetails::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function withTrashedSale()
    {
        return $this->hasOne(Sale::class, 'id', 'sale_id')->withTrashed();
    }
}
