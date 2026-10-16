<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Factory;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Departments actually found in the legacy "Daily Report" sheet,
     * with the misspelled "Maintance" normalized to "Maintenance" while
     * preserving the original label for traceability. "Q" is kept as-is
     * since its meaning was not explained in the source workbook.
     * Seeded identically for every factory — each factory keeps its own
     * copy of these rows, editable independently from here on.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Sewing', 'legacy_label' => 'Sweing'],
            ['name' => 'A. Op Sewing', 'legacy_label' => 'A. Op Sweing'],
            ['name' => 'Maintenance', 'legacy_label' => 'Maintenance / Maintance'],
            ['name' => 'Store', 'legacy_label' => 'Store'],
            ['name' => 'Loader', 'legacy_label' => 'Loader'],
            ['name' => 'Q', 'legacy_label' => 'Q (meaning not specified in source workbook)'],
        ];

        foreach (Factory::orderBy('id')->get() as $factory) {
            foreach ($departments as $d) {
                Department::firstOrCreate(['factory_id' => $factory->id, 'name' => $d['name']], [...$d, 'factory_id' => $factory->id]);
            }
        }
    }
}
