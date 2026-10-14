<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Departments actually found in the legacy "Daily Report" sheet,
     * with the misspelled "Maintance" normalized to "Maintenance" while
     * preserving the original label for traceability. "Q" is kept as-is
     * since its meaning was not explained in the source workbook.
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

        foreach ($departments as $d) {
            Department::firstOrCreate(['name' => $d['name']], $d);
        }
    }
}
