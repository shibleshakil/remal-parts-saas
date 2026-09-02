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
        Schema::table('quote_details', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_details', function (Blueprint $table) {
            $table->foreign('item_id')
                ->references('id')
                ->on('vehicle_details')
                ->onDelete('cascade');
        });
    }
};
