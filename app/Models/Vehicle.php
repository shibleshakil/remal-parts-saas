<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class Vehicle extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'name',
        'uid',
        'barcode',
        'model',
        'chasis_no',
        'engine_no',
        'date',
        'total_purchase_price',
        'transaction_id',
        'total_vehicle_price',
        'target_sell_price',
        'note',
        'qr_code',
        'supplier_id',
        'invoice_no',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(VehicleDetails::class);
    }

        /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'barcode',
    ];

    protected static function boot()
    {
        parent::boot();

        // Listen for the 'creating' event and generate the uid
        static::creating(function ($sample) {
            // Check if uid is not already set
            static::generateUidAndBarcode($sample);
            static::generateQrCode($sample);
        });

        // Update barcode when updating
        static::updating(function ($sample) {
            static::generateUidAndBarcode($sample);
            static::generateQrCode($sample);
            Log::info('Updating model with UID: ' . $sample->vehicle_target_sell_price);
        });
    }

    protected static function generateUidAndBarcode($sample)
    {
        // Generate UID if not already set
        if (!$sample->uid) {
            $total = self::withTrashed()->count();
            $uid = $total + 1;
            $sample->uid = "CAR" . sprintf('%03d', $uid) . "-B". $sample->branch_id;
        }

        // Generate barcode path if not exists
        if (!$sample->barcode) {
            $barcodeFileName = 'barcode_' . $sample->uid . '.png';
            $barcodePath = 'barcodes/' . $barcodeFileName; // Path in public folder
            $fullPath = public_path('storage/' . $barcodePath); // Absolute path in public folder

            try {
                // Create an instance of DNS1D for barcode generation
                $barcode = new DNS1D();
                $barcodeImage = base64_decode($barcode->getBarcodePNG($sample->uid, 'C39')); // Decode base64

                // Ensure the directory exists
                $directory = public_path('storage/barcodes');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Save the barcode image in the public folder
                file_put_contents($fullPath, $barcodeImage);

                // Save the relative path in the database
                $sample->barcode = $barcodePath;
            } catch (\Throwable $e) {
                // Log the error and continue processing
                Log::error('Barcode generation failed for UID: ' . $sample->uid . '. Error: ' . $e->getMessage());

                // Fallback - leave barcode null or empty
                $sample->barcode = null;
            }
        }
    }


    protected static function generateQrCode($sample)
    {

        // Generate QR code path if not exists
        $qrFileName = 'qr_' . $sample->uid . '.png';
        $qrPath = 'qrcodes/' . $qrFileName; // Path in storage/public/qrcodes
        $fullPath = public_path('storage/' . $qrPath);

        try {
            // Create an instance of DNS2D for QR generation
            $qr = new DNS2D();
            $qrImage = base64_decode(
                $qr->getBarcodePNG($sample->vehicle_target_sell_price, 'QRCODE') // generate QR
            );

            $directory = public_path('storage/qrcodes');

            if (!file_exists($directory)) {
                Log::info('dir make');
                mkdir($directory, 0755, true);
            }

            if ($sample->qr_code) {
                Log::info('has qr');
                // If old QR exists, delete it
                if (file_exists($fullPath)) {

                    unlink($fullPath);
                    Log::info('delete old one');
                }
            }

            // Save the QR image
            file_put_contents($fullPath, $qrImage);

            // Save relative path in DB
            $sample->qr_code = $qrPath;

            Log::info($qrPath);
            Log::info($sample->qr_code);
        } catch (\Throwable $e) {
            Log::error('QR code generation failed for UID: ' . $sample->uid . '. Error: ' . $e->getMessage());
            $sample->qr_code = null; // fallback
        }
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetails::class, 'vehicle_id');
    }

    public function sales()
    {
        return $this->hasManyThrough(
            Sale::class,
            SaleDetails::class,
            'vehicle_id',
            'id',
            'id',
            'sale_id'
        );
    }

    public function getTotalSaleAmountAttribute()
    {
        return (float) DB::table('sales')
            ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->where('sale_details.vehicle_id', $this->id)
            ->sum(DB::raw('sale_details.unit_price * sale_details.quantity'));
    }

    public function getSaleItemsPurchasePriceAttribute()
    {
        $items = DB::table('sale_details')
            ->where('vehicle_id', $this->id)
            ->select('sale_details.*')
            ->get();

        return (float) $items->sum(fn ($item) => $item->item_actual_price * $item->quantity);
    }

    public function purchaseTransaction(){
        return $this->hasOne(Transaction::class, 'id', 'transaction_id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function payments(){
        return $this->hasMany(Transaction::class);
    }

}
