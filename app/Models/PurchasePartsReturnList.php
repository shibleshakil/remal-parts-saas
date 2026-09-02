<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePartsReturnList extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_line_id',
        'invoice_no',
        'item_name',
        'quantity',
        'unit_price',
        'return_amount',
    ];

    public function purchaseLine()
    {
        return $this->belongsTo(PurchaseLines::class);
    }
}

