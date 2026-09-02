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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('transaction_date');
            $table->unsignedBigInteger('bank_id')->unsigned()->nullable()->after('payment_method');

            $table->foreign('bank_id')
                  ->references('id')
                  ->on('banks')
                  ->onDelete('cascade');

            $table->double('paid_amount')->nullable()->after('final_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropForeign(['bank_id']);
            $table->dropColumn('bank_id');
            $table->dropColumn('paid_amount');
        });
    }
};
