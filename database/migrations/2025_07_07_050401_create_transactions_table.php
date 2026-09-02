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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trxid')->unique();
            $table->string('type')->nullable();

            $table->unsignedBigInteger('expense_id')->index()->nullable();
            $table->unsignedBigInteger('sale_id')->index()->nullable();
            $table->date('transaction_date')->nullable();
            $table->double('final_amount')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();


            $table->unsignedBigInteger('created_by')->index()->nullable();
            $table->unsignedBigInteger('updated_by')->index()->nullable();
            $table->unsignedBigInteger('deleted_by')->index()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
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
        Schema::dropIfExists('transactions');
    }
};
