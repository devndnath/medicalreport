<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            FactorySeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            MedicineCategorySeeder::class,
            MedicineSeeder::class,
            SupplierSeeder::class,
            OpeningStockSeeder::class,
        ]);
    }
}
