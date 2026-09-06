<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseLines extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['vehicle_detail_id', 'quantity', 'unit_price', 'purchase_date',
        'transaction_id', 'target_sell_price', 'sold_quantity', 'return_quantity', 'supplier_id',
        'invoice_no',  'bulk_purchase', 'attachment','branch_id'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $casts = [
        'purchase_date' => 'date',
    ];


    public function vehicleDetail()
    {
        return $this->belongsTo(VehicleDetails::class);
    }

    public function transaction(){
        return $this->hasOne(Transaction::class, 'id', 'transaction_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class,'purchase_invoice_no', 'invoice_no');

    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
}
