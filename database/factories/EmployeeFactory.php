<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = \App\Models\Employee::class;

    public function definition(): array
    {
        return [
            'employee_code' => Str::upper(Str::random(6)),
            'name' => $this->faker->name(),
            'employment_status' => 'active',
        ];
    }
}
