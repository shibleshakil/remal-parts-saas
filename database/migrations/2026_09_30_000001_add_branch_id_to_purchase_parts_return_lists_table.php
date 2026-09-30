<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_parts_return_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->index('branch_id');
        });

        // Backfill existing rows from their purchase line's branch.
        // Chunked loop keeps this portable across MySQL and SQLite.
        DB::table('purchase_parts_return_lists')
            ->whereNull('branch_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $branchId = DB::table('purchase_lines')
                        ->where('id', $row->purchase_line_id)
                        ->value('branch_id');

                    if ($branchId) {
                        DB::table('purchase_parts_return_lists')
                            ->where('id', $row->id)
                            ->update(['branch_id' => $branchId]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_parts_return_lists', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
