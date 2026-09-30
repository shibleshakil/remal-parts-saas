<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Super Admin accounts intentionally have no home branch (global view),
     * so users.branch_id must be nullable. Branch-bound staff always get a
     * branch assigned at creation (validated in UserController).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Re-point any branchless users before restoring NOT NULL.
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('branch_id')
            ->update(['branch_id' => 1]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
        });
    }
};
