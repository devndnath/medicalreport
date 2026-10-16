<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Database\Seeder;

class OpeningStockSeeder extends Seeder
{
    /**
     * Seed opening stock (medicine batches) for each medicine in each factory.
     * This creates initial inventory with realistic quantities and costs.
     */
    public function run(): void
    {
        foreach (Factory::orderBy('id')->get() as $factory) {
            $this->seedForFactory($factory);
        }
    }

    protected function seedForFactory(Factory $factory): void
    {
        $medicines = Medicine::where('factory_id', $factory->id)->get();
        $baseDate = now()->subMonths(2);

        foreach ($medicines as $medicine) {
            // Generate opening stock with 2-4 batches per medicine
            $batchCount = rand(2, 4);

            for ($i = 1; $i <= $batchCount; $i++) {
                $receivedDate = $baseDate->copy()->addDays(($i - 1) * 15);
                $quantity = $this->getQuantityForMedicine($medicine->base_unit);

                MedicineBatch::firstOrCreate(
                    [
                        'medicine_id' => $medicine->id,
                        'batch_number' => 'B'.str_pad($medicine->id, 4, '0', STR_PAD_LEFT).'-'.sprintf('%02d', $i),
                    ],
                    [
                        'factory_id' => $factory->id,
                        'medicine_id' => $medicine->id,
                        'batch_number' => 'B'.str_pad($medicine->id, 4, '0', STR_PAD_LEFT).'-'.sprintf('%02d', $i),
                        'received_date' => $receivedDate->toDateString(),
                        'received_quantity' => $quantity,
                        'remaining_quantity' => $quantity,
                        'unit_cost' => $this->getPriceForMedicine($medicine->name, $medicine->strength),
                        'expiry_date' => $receivedDate->copy()->addMonths(rand(24, 48))->toDateString(),
                        'is_opening_balance' => true,
                        'status' => 'active',
                        'remarks' => 'Opening stock seeded for initial inventory tracking.',
                    ]
                );
            }
        }
    }

    /**
     * Return realistic opening quantity based on unit type.
     */
    protected function getQuantityForMedicine(string $unit): int
    {
        return match ($unit) {
            'tablet' => rand(100, 500),
            'capsule' => rand(100, 300),
            'ml' => rand(500, 2000),
            'bottle' => rand(5, 20),
            'tube' => rand(10, 30),
            'sachet' => rand(50, 200),
            'strip' => rand(20, 100),
            'packet' => rand(5, 15),
            'roll' => rand(3, 10),
            'piece' => rand(10, 50),
            default => rand(50, 200),
        };
    }

    /**
     * Return realistic unit cost based on medicine name and strength.
     * These are approximate wholesale costs in BDT.
     */
    protected function getPriceForMedicine(?string $name, ?string $strength): float
    {
        $name = strtolower($name ?? '');

        // Tablets & Capsules: 0.50 - 5.00 BDT per unit
        if (str_contains($name, 'napa') || str_contains($name, 'paracetamol')) {
            return round(rand(50, 150) / 100, 2);
        }
        if (str_contains($name, 'ors')) {
            return round(rand(200, 400) / 100, 2);
        }
        if (str_contains($name, 'entacyd') || str_contains($name, 'amodis')) {
            return round(rand(200, 500) / 100, 2);
        }
        if (str_contains($name, 'alzine') || str_contains($name, 'omidon') || str_contains($name, 'rolac')) {
            return round(rand(100, 300) / 100, 2);
        }
        if (str_contains($name, 'fenadin')) {
            return round(rand(150, 400) / 100, 2);
        }
        if (str_contains($name, 'viodin')) {
            return round(rand(80, 200) / 100, 2);
        }

        // Syrups: 0.50 - 2.00 per ml
        if (str_contains($name, 'syrup')) {
            return round(rand(50, 200) / 100, 2);
        }

        // Gels/Creams: 2.00 - 15.00 per tube
        if (str_contains($name, 'volinac') || str_contains($name, 'savlon') || str_contains($name, 'trego') || str_contains($name, 'burna') || str_contains($name, 'clofenac')) {
            return round(rand(200, 1500) / 100, 2);
        }

        // Drops: 5.00 - 20.00 per bottle
        if (str_contains($name, 'optimox')) {
            return round(rand(500, 2000) / 100, 2);
        }

        // Liquids/Antiseptics: 1.00 - 10.00 per bottle
        if (str_contains($name, 'hexisol') || str_contains($name, 'savlon')) {
            return round(rand(100, 1000) / 100, 2);
        }

        // First-Aid Supplies
        if (str_contains($name, 'green strip')) {
            return round(rand(150, 300) / 100, 2);
        }
        if (str_contains($name, 'cotton')) {
            return round(rand(50, 200) / 100, 2);
        }
        if (str_contains($name, 'surgical gauze')) {
            return round(rand(100, 300) / 100, 2);
        }
        if (str_contains($name, 'roll bandage')) {
            return round(rand(200, 500) / 100, 2);
        }
        if (str_contains($name, 'nix')) {
            return round(rand(100, 300) / 100, 2);
        }

        // Default fallback
        return round(rand(100, 500) / 100, 2);
    }
}
