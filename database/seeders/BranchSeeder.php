<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $code = str_pad(Branch::max('id') + 1, 4, '0', STR_PAD_LEFT);
        DB::table('branches')->insert([
            [
                "id" => 1,
                "name" => "Main Branch",
                "code" => $code,
                "phone" => "1234567890",
                "email" => "admin@admin.com",
                "address" => "Kohinur Tower, RD:7, Gulshan 1, Dhaka, Bangladesh",
                "created_at" => now(),
                "updated_at" => now(),
            ]
        ]);
    }
}
