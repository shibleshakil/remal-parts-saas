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
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->bigInteger('transaction_id')->nullable()->after('vehicle_detail_id');
            $table->string('target_sell_price', 100)->nullable()->after('transaction_id');
            $table->double('sold_quantity')->nullable()->after('quantity');
            $table->double('return_quantity')->nullable()->after('sold_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
            $table->dropColumn('target_sell_price');
            $table->dropColumn('sold_quantity');
            $table->dropColumn('return_quantity');
        });
    }
};
