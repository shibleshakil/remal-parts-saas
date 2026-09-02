<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'trxid',
        'type',
        'expense_id',
        'sale_id',
        'purchase_invoice_no',
        'customer_id',
        'vehicle_id',
        'vehicle_details_id',
        'transaction_date',
        'final_amount',
        'payment_method',
        'status',
        'note',
        'supplier_id',
        'payment_method',
        'bank_id',
        'paid_amount',
        'current_balance'
    ];

    public function bank(){
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function sale(){
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function expense(){
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function getInvoiceNumberAttribute()
    {
        return match ($this->type) {

            'sale',
            'refund',
            'payment' => '-- #' . optional($this->sale)->invoice_no,

            'purchase',
            'purchase_return',
            'supplier_payment',
            'spare_part_purchase',
            'bulk_purchase',
            'vehicle_purchase' => '-- #' . $this->purchase_invoice_no,

            'expense' => '-- #' . optional($this->expense)->title,

            default => null,
        };
    }
}
