<?php

namespace Database\Seeders;

use App\Models\Factory;
use Illuminate\Database\Seeder;

class FactorySeeder extends Seeder
{
    public function run(): void
    {
        Factory::firstOrCreate(['code' => 'F1'], ['name' => 'Factory 1', 'is_active' => true]);
        Factory::firstOrCreate(['code' => 'F2'], ['name' => 'Factory 2', 'is_active' => true]);
    }
}
