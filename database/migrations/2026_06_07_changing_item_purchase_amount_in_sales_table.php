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
        Schema::table('sales', function (Blueprint $table) {
            // Change the column type to decimal with precision
            $table->double('item_purchase_amount', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Rollback: Change it back to string (varchar)
            $table->string('item_purchase_amount')->change();
        });
    }
};
