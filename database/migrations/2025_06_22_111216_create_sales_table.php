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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('customer_id')->unsigned()->index()->nullable();
            $table->date('date')->nullable();
            $table->string('payment_method')->nullable();
            $table->double('total_cost', 10, 2)->nullable();
            $table->double('discount_amount', 10, 2)->default(0)->nullable();
            $table->string('discount_type')->default('flat')->nullable();
            $table->double('final_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

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
        Schema::dropIfExists('sales');
    }
};
