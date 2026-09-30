<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite rebuilds the table for dropColumn, which drops the
            // inline FK automatically; explicit dropForeign is unsupported.
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('vehicle_id');
            });

            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->unsigned()->index()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }
};
