<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'vehicle_id', 'customer_id', 'date', 'payment_method','invoice_no','bank_id','branch_id', 'vat', 'vat_amount', 'vat_type',
        'total_cost', 'discount_amount', 'discount_type', 'final_amount','paid_amount','item_purchase_amount','send_email'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    public static function boot(){
        parent::boot();
        static::creating(function ($sale) {
            $year = date('Y');
            $total = self::withoutGlobalScope(BranchScope::class)
                    ->withTrashed()
                    ->whereYear('date', $year)
                    ->count() + 1;

            $sale->invoice_no = $year . $total;
        });
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function items()
    {
        return $this->hasMany(SaleDetails::class);
    }
    public function saleDetails()
    {
        return $this->hasMany(SaleDetails::class, 'sale_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'sale_id');
    }
}
