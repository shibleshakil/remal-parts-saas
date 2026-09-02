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
        Schema::table('vehicle_details', function (Blueprint $table) {
            $table->after('vehicle_id', function(Blueprint $table){
                $table->string('uid')->nullable();
                $table->string('barcode')->nullable();
                $table->string('car_name')->nullable();
                $table->string('car_model')->nullable();
                $table->date('date')->nullable();
                $table->text('note')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_details', function (Blueprint $table) {

            $table->dropColumn(['uid', 'barcode', 'car_name', 'car_model', 'date', 'note']);
        });
    }
};
