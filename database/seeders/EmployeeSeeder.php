<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Factory;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Factory::orderBy('id')->get() as $factory) {
            $this->seedForFactory($factory);
        }
    }

    protected function seedForFactory(Factory $factory): void
    {
        $employees = [
            // Sewing Department
            [
                'employee_code' => 'EMP-001',
                'name' => 'Abdul Karim',
                'department' => 'Sewing',
                'designation' => 'Sewing Operator',
                'phone' => '01712345601',
                'joining_date' => '2022-03-15',
            ],
            [
                'employee_code' => 'EMP-002',
                'name' => 'Fatima Begum',
                'department' => 'Sewing',
                'designation' => 'Sewing Operator',
                'phone' => '01712345602',
                'joining_date' => '2022-05-20',
            ],
            [
                'employee_code' => 'EMP-003',
                'name' => 'Mohammad Hasan',
                'department' => 'Sewing',
                'designation' => 'Senior Operator',
                'phone' => '01712345603',
                'joining_date' => '2021-01-10',
            ],
            // A. Op Sewing Department
            [
                'employee_code' => 'EMP-004',
                'name' => 'Jesmin Akter',
                'department' => 'A. Op Sewing',
                'designation' => 'Quality Checker',
                'phone' => '01712345604',
                'joining_date' => '2022-02-14',
            ],
            [
                'employee_code' => 'EMP-005',
                'name' => 'Khalid Ahmed',
                'department' => 'A. Op Sewing',
                'designation' => 'Assembly Operator',
                'phone' => '01712345605',
                'joining_date' => '2022-06-01',
            ],
            // Maintenance Department
            [
                'employee_code' => 'EMP-006',
                'name' => 'Rahman Ali',
                'department' => 'Maintenance',
                'designation' => 'Maintenance Engineer',
                'phone' => '01712345606',
                'joining_date' => '2020-08-22',
            ],
            [
                'employee_code' => 'EMP-007',
                'name' => 'Karim Hassan',
                'department' => 'Maintenance',
                'designation' => 'Technician',
                'phone' => '01712345607',
                'joining_date' => '2021-11-05',
            ],
            // Store Department
            [
                'employee_code' => 'EMP-008',
                'name' => 'Nasir Uddin',
                'department' => 'Store',
                'designation' => 'Store Keeper',
                'phone' => '01712345608',
                'joining_date' => '2021-04-18',
            ],
            [
                'employee_code' => 'EMP-009',
                'name' => 'Amina Khan',
                'department' => 'Store',
                'designation' => 'Store Assistant',
                'phone' => '01712345609',
                'joining_date' => '2023-01-09',
            ],
            // Loader Department
            [
                'employee_code' => 'EMP-010',
                'name' => 'Habib Miah',
                'department' => 'Loader',
                'designation' => 'Loader',
                'phone' => '01712345610',
                'joining_date' => '2022-07-11',
            ],
            [
                'employee_code' => 'EMP-011',
                'name' => 'Shahadat Khan',
                'department' => 'Loader',
                'designation' => 'Loader',
                'phone' => '01712345611',
                'joining_date' => '2022-09-30',
            ],
            // Q Department
            [
                'employee_code' => 'EMP-012',
                'name' => 'Arif Islam',
                'department' => 'Q',
                'designation' => 'Quality Control Officer',
                'phone' => '01712345612',
                'joining_date' => '2021-03-25',
            ],
        ];

        foreach ($employees as $data) {
            $dept = Department::where('factory_id', $factory->id)
                ->where('name', $data['department'])
                ->first();

            Employee::firstOrCreate(
                [
                    'factory_id' => $factory->id,
                    'employee_code' => $data['employee_code'].'-F'.$factory->id,
                ],
                [
                    'factory_id' => $factory->id,
                    'employee_code' => $data['employee_code'].'-F'.$factory->id,
                    'name' => $data['name'],
                    'department_id' => $dept?->id,
                    'designation' => $data['designation'],
                    'employment_status' => 'active',
                    'phone' => $data['phone'],
                    'joining_date' => $data['joining_date'],
                    'remarks' => 'Seeded employee for testing and reporting.',
                ]
            );
        }
    }
}
