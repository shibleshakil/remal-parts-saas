<?php

namespace App\Models;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'uid',
        'barcode',
        'qr_code',
        'target_sell_price',
        'parts_name',
        'car_name',
        'car_model',
        'parts_quantity',
        'unit_price',
        'total_parts_price',
        'date',
        'note',
    ];

    protected $hidden = ['barcode'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sample) {
            static::generateUidAndBarcode($sample);
            static::generateQrCode($sample);
        });

        static::updating(function ($sample) {
            Log::info('Updating VehicleDetails with UID: ' . $sample->uid);
            static::generateUidAndBarcode($sample);
            static::generateQrCode($sample);
        });
    }

    protected static function generateUidAndBarcode($sample)
    {
        if (!$sample->uid) {
            $total = self::withTrashed()->count();
            $uid = $total + 1;
            $sample->uid = "SP" . sprintf('%03d', $uid);
        }

        if (!$sample->barcode) {
            $barcodeFileName = 'barcode_' . $sample->uid . '.png';
            $barcodePath = 'barcodes/' . $barcodeFileName;
            $fullPath = public_path('storage/' . $barcodePath);

            try {
                $barcode = new DNS1D();
                $barcodeImage = base64_decode($barcode->getBarcodePNG($sample->uid, 'C39'));

                $directory = public_path('storage/barcodes');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                file_put_contents($fullPath, $barcodeImage);
                $sample->barcode = $barcodePath;
            } catch (\Throwable $e) {
                Log::error('Barcode generation failed for UID: ' . $sample->uid . '. Error: ' . $e->getMessage());
                $sample->barcode = null;
            }
        }
    }


    protected static function generateQrCode($sample)
    {
        if ($sample->vehicle_id) {
            $sample->qr_code = NULL;
        }else{
            // Generate QR code path if not exists
            $qrFileName = 'qr_' . $sample->uid . '.png';
            $qrPath = 'qrcodes/' . $qrFileName;
            $fullPath = public_path('storage/vehicle_details/' . $qrPath);

            try {
                // Create an instance of DNS2D for QR generation
                $qr = new DNS2D();
                $qrImage = base64_decode(
                    $qr->getBarcodePNG($sample->target_sell_price, 'QRCODE') // generate QR
                );

                $directory = public_path('storage/vehicle_details/qrcodes/');

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                if ($sample->qr_code) {
                    // If old QR exists, delete it
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }

                // Save the QR image
                file_put_contents($fullPath, $qrImage);

                // Save relative path in DB
                $sample->qr_code = $qrPath;
            } catch (\Throwable $e) {
                Log::error('QR code generation failed for UID: ' . $sample->uid . '. Error: ' . $e->getMessage());
                $sample->qr_code = null; // fallback
            }
        }
    }

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetails::class, 'item_id')
            ->whereIn('item_type', ['spare_part', 'car_spare_part']);
    }

    public function quoteDetails()
    {
        return $this->hasMany(QuoteDetails::class, 'item_id')
            ->whereIn('item_type', ['spare_part', 'car_spare_part']);
    }

   public function purchaseLines()
    {
        return $this->hasMany(PurchaseLines::class, 'vehicle_detail_id');
    }


    public function getTotalSaleAmountAttribute()
    {
        return $this->saleDetails()->sum('cost') ?? 0;
    }

    public function getTotalSoldQuantityAttribute()
    {
        return $this->saleDetails()->sum('quantity') ?? 0;
    }

    public function getPartsQuantityAttribute()
    {
        return $this->purchaseLines()->sum('quantity');
    }

    public function getAveragePriceAttribute()
    {
        return $this->purchaseLines()->avg('unit_price');
    }
}
