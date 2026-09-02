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
        Schema::create('vehicle_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->unsigned()->index()->nullable();
            $table->string('product_name');
            $table->double('quantity');
            $table->double('price');
            $table->double('vehicle_price');
            $table->timestamps();

            $table->softDeletes();


            $table->unsignedBigInteger('created_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('updated_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('deleted_by')->unsigned()->index()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');


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
        Schema::dropIfExists('vehicle_details');
    }
};
