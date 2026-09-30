<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePartsReturnList extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'purchase_line_id',
        'invoice_no',
        'item_name',
        'quantity',
        'unit_price',
        'return_amount',
        'branch_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    public function purchaseLine()
    {
        return $this->belongsTo(PurchaseLines::class);
    }
}

