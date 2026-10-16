<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class MedicineCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Factory::orderBy('id')->get() as $factory) {
            foreach (['Tablet/Capsule', 'Syrup', 'Topical (Gel/Cream)', 'Drop', 'Liquid/Antiseptic', 'First-Aid Supply'] as $name) {
                MedicineCategory::firstOrCreate(['factory_id' => $factory->id, 'name' => $name]);
            }
        }
    }
}
