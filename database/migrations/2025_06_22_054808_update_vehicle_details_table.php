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
        // One column per Schema call: SQLite allows only a single
        // renameColumn per table modification.
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->renameColumn('product_name', 'parts_name');
        });
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->renameColumn('quantity', 'parts_quantity');
        });
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->renameColumn('price', 'unit_price');
        });
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->renameColumn('vehicle_price', 'total_parts_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->renameColumn('parts_name', 'product_name');
            $table->renameColumn('unit_price', 'price');
            $table->renameColumn('parts_quantity', 'quantity');
            $table->renameColumn('total_parts_price', 'vehicle_price');
        });
    }
};
