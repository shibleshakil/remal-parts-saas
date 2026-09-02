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
        Schema::create('business_locations', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("email")->unique();
            $table->string("mobile")->nullable();
            $table->string("landmark")->nullable();
            $table->string("country")->nullable();
            $table->string("state")->nullable();
            $table->string("city")->nullable();
            $table->string("zipcode")->nullable();

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
