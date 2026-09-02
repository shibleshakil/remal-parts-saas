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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('customer_id');
            $table->unsignedBigInteger('vehicle_details_id')->nullable()->after('vehicle_id');

            // If you want foreign keys (optional)
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->foreign('vehicle_details_id')->references('id')->on('vehicle_details')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop foreign keys first if used
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['vehicle_details_id']);

            $table->dropColumn([
                'vehicle_id',
                'vehicle_details_id'
            ]);
        });
    }
};
