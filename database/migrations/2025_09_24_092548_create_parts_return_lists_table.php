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
        Schema::create('parts_return_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_detail_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('item_name')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('unit_price')->nullable();
            $table->integer('refund_amount')->nullable();
            $table->timestamps();

            // Foreign keys (optional)
            $table->foreign('vehicle_detail_id')->references('id')->on('vehicle_details')->onDelete('cascade');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts_return_lists');
    }
};
