<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * MasterSeeder creates: Main Branch, Super Admin (branchless = global
     * view), and the Super Admin role with one permission per named route.
     * Re-run after any route change to re-sync permissions + labels:
     *   php artisan db:seed
     */
    public function run(): void
    {
        $this->call([
            MasterSeeder::class,
        ]);
    }
}
