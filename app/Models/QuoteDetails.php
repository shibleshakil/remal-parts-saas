<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function partsDetail()
    {
        return $this->belongsTo(VehicleDetails::class, 'item_id');
    }
}
