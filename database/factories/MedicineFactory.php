<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MedicineFactory extends Factory
{
    protected $model = \App\Models\Medicine::class;

    public function definition(): array
    {
        return [
            'sku' => Str::upper(Str::random(8)),
            'name' => $this->faker->word(),
            'dosage_form' => 'tablet',
            'base_unit' => 'tablet',
            'purchase_unit' => 'box',
            'issue_unit' => 'tablet',
            'units_per_purchase_pack' => 1,
            'minimum_stock_level' => 0,
            'reorder_level' => 10,
            'status' => 'active',
        ];
    }
}
