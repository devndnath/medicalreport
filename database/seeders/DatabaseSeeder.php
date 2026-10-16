<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Core data every installation needs.
        $this->call([
            RoleSeeder::class,
            FactorySeeder::class,
            UserSeeder::class,
            ApplicationSettingSeeder::class,
            DepartmentSeeder::class,
            MedicineCategorySeeder::class,
            MedicineSeeder::class,
        ]);

        // Sample employees, suppliers, stock and transactions — never in production.
        if (! app()->isProduction()) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
