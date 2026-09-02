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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_type')->nullable();
            $table->string('item_name')->nullable();
            $table->double('unit_price', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->double('cost', 10, 2)->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('updated_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('deleted_by')->unsigned()->index()->nullable();


            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
