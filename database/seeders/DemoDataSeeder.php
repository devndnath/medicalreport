<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Optional sample data for trying the system out: extra users, employees,
 * suppliers, opening stock and ~45 days of purchases, issues, returns,
 * adjustments and a physical count for every factory.
 *
 * Run on its own with:  php artisan db:seed --class=DemoDataSeeder
 * (needs the core seeders to have run first). DatabaseSeeder runs it
 * automatically everywhere except production.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            EmployeeSeeder::class,
            SupplierSeeder::class,
            OpeningStockSeeder::class,
            SampleActivitySeeder::class,
        ]);
    }
}
