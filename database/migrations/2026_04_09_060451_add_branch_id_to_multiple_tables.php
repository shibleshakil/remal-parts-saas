<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'balances',
        'banks',
        'business_locations',
        'countries',
        'customers',
        'expenses',
        'expense_types',
        'parts_return_lists',
        'purchase_lines',
        'quotes',
        'quote_details',
        'sales',
        'sale_details',
        'spare_parts',
        'suppliers',
        'transactions',
        'users',
        'vehicles',
        'vehicle_details',
        // add more tables here
    ];

    public function up(): void
    {
        // Step 1: Add nullable column
        foreach ($this->tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->default(1)->nullable()->after('id');
            });
        }

        // Step 2: Update existing data
        foreach ($this->tables as $tbl) {
            DB::table($tbl)->update([
                'branch_id' => 1 // default branch id
            ]);
        }

        // Step 3: Make NOT NULL + add foreign key
        foreach ($this->tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {

                // change to NOT NULL
                $table->unsignedBigInteger('branch_id')->nullable(false)->change();

                // add foreign key
                $table->foreign('branch_id')
                      ->references('id')
                      ->on('branches')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {

                // drop foreign key correctly
                $table->dropForeign(['branch_id']);

                // drop column
                $table->dropColumn('branch_id');
            });
        }
    }
};
