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
            $table->boolean('bulk_purchase')->nullable()->default(false)->after('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropColumn('bulk_purchase');
        });
    }
};
