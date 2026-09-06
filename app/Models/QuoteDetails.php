<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteDetails extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_id',
        'item_id',
        'vehicle_id',
        'purchase_line_id',
        'item_type',
        'item_name',
        'unit_price',
        'quantity',
        'cost',
        'created_by',
        'updated_by',
        'deleted_by',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function purchaseLine()
    {
        return $this->belongsTo(PurchaseLines::class, 'purchase_line_id');
    }

    public function partsDetail()
    {
        return $this->belongsTo(VehicleDetails::class, 'item_id');
    }
}
