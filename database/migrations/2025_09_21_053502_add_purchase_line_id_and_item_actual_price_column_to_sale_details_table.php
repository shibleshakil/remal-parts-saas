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
        Schema::table('sale_details', function (Blueprint $table) {
            $table->string('purchase_line_id')->nullable()->after('vehicle_id');
            $table->decimal('item_actual_price', 10, 2)->nullable()->after('item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('purchase_line_id');
            $table->dropColumn('item_actual_price');
        });
    }
};
