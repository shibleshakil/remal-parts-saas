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
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');


            $table->unsignedBigInteger('created_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('updated_by')->unsigned()->index()->nullable();
            $table->unsignedBigInteger('deleted_by')->unsigned()->index()->nullable();

            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('expense_types');
    }
};
