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
        Schema::create('purchase_parts_return_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_line_id');
            $table->string('invoice_no')->nullable();
            $table->string('item_name')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('unit_price')->nullable();
            $table->integer('return_amount')->nullable();
            $table->timestamps();

            // Foreign keys (optional)
            $table->foreign('purchase_line_id')->references('id')->on('purchase_lines')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_parts_return_lists');
    }
};
