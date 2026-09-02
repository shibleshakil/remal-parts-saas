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
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_type_id')->index()->nullable();
            $table->foreign('expense_type_id')
                ->references('id')
                ->on('expense_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_type_id']);
            $table->dropColumn('expense_type_id');
        });
    }
};
