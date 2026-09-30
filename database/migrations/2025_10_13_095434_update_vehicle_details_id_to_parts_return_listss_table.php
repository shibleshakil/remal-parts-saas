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
            // SQLite has no named FK index to drop; inline FKs are removed
            // automatically when the column is dropped in a later migration.
            return;
        }

        Schema::table('parts_return_lists', function (Blueprint $table) {
             $table->dropForeign(['vehicle_detail_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts_return_lists', function (Blueprint $table) {
            $table->foreign('vehicle_detail_id')
                  ->references('id')
                  ->on('vehicle_details')
                  ->onDelete('cascade');
        });
    }
};
