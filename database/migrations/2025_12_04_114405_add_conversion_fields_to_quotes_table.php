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
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('id');
            $table->unsignedBigInteger('sale_id')->nullable()->after('status');
            $table->timestamp('converted_at')->nullable()->after('sale_id');
            $table->unsignedBigInteger('converted_by')->nullable()->after('converted_at');

            // If you want foreign keys (optional)
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            $table->foreign('converted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Drop foreign keys first if used
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['converted_by']);

            $table->dropColumn([
                'status',
                'sale_id',
                'converted_at',
                'converted_by'
            ]);
        });
    }
};
