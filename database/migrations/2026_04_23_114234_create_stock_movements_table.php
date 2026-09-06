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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->float('unit_price', 15, 2)->default(0)->nullable();
            $table->integer('quantity');
            $table->float('total_amount', 15, 2)->default(0)->nullable();
            $table->unsignedBigInteger('purchase_line_id')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('branch_from_id')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('branch_to_id')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('transaction_id')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('created_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('updated_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('deleted_by')->unsigned()->index()->nullable();

            $table->foreign('purchase_line_id')->references('id')->on('purchase_lines')->onDelete('cascade');
            $table->foreign('branch_from_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('branch_to_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
