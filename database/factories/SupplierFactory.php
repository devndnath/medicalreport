<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = \App\Models\Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'status' => 'active',
        ];
    }
}
