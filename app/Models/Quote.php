<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'date',
        'quote_no',
        'total_cost',
        'discount_amount',
        'discount_type',
        'final_amount',
        'warranty',
        'expected_purchase_date',
        'status',
        'sale_id',
        'converted_at',
        'converted_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static function boot(){
        parent::boot();
        static::creating(function ($quote) {
            $year = date('Y');
            $total = self::withTrashed()
                ->whereYear('date', $year)
                ->count() + 1;

            $quote->quote_no = $year . $total;
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
        return $this->hasMany(QuoteDetails::class);
    }
    public function quoteDetails()
    {
        return $this->hasMany(QuoteDetails::class, 'quote_id');
    }
}
