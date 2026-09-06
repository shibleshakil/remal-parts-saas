<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{

protected $fillable = [
        'quantity',
        'unit_price',
        'total_amount',
        'purchase_line_id',
        'branch_from_id',
        'branch_to_id',
        'transaction_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    use HasFactory;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function purchaseLine()
    {
        return $this->belongsTo(PurchaseLines::class, 'purchase_line_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_from_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_to_id');
    }
}
