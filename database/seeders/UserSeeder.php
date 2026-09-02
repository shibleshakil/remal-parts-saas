<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                "id" => 1,
                "name" => "Super Admin",
                "email" => "admin@admin.com",
                'role' => 'admin',
                "password" => bcrypt('Admin@123'),
                "email_verified_at" => now(),
                "created_at" => now(),
                "updated_at" => now(),
            ]
        ]);
    }
}
