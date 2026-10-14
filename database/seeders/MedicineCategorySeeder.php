<?php

namespace Database\Seeders;

use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;

class MedicineCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tablet/Capsule', 'Syrup', 'Topical (Gel/Cream)', 'Drop', 'Liquid/Antiseptic', 'First-Aid Supply'] as $name) {
            MedicineCategory::firstOrCreate(['name' => $name]);
        }
    }
}
