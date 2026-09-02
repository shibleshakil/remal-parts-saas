<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('uid')->unique()->nullable()->after('engine_no'); // Add uid column, unique and nullable
            $table->string('barcode')->nullable()->after('uid');
            $table->double('total_purchase_price')->nullable()->after('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['uid', 'barcode','total_purchase_price']);
        });
    }
};
