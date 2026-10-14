<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MedicineSeeder extends Seeder
{
    /**
     * The exact 23 items from the legacy workbook's "Medicine List" sheet,
     * split into name/strength and given a sensible dosage form, base
     * unit, and category. No purchase price is set here — prices are
     * entered through the opening-stock/purchase screens, never invented.
     */
    public function run(): void
    {
        $tablet = MedicineCategory::where('name', 'Tablet/Capsule')->first();
        $syrup = MedicineCategory::where('name', 'Syrup')->first();
        $topical = MedicineCategory::where('name', 'Topical (Gel/Cream)')->first();
        $drop = MedicineCategory::where('name', 'Drop')->first();
        $liquid = MedicineCategory::where('name', 'Liquid/Antiseptic')->first();
        $supply = MedicineCategory::where('name', 'First-Aid Supply')->first();

        $items = [
            ['name' => 'Napa', 'strength' => '500mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'ORS', 'strength' => null, 'dosage_form' => 'other', 'unit' => 'sachet', 'category_id' => $tablet->id],
            ['name' => 'Entacyd Plus', 'strength' => '400mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Alzine', 'strength' => '50mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Omidon', 'strength' => '10mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Fenadin', 'strength' => '120mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Viodin', 'strength' => null, 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Amodis DS', 'strength' => '400mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Rolac', 'strength' => '10mg', 'dosage_form' => 'tablet', 'unit' => 'tablet', 'category_id' => $tablet->id],
            ['name' => 'Green Strip', 'strength' => null, 'dosage_form' => 'supply', 'unit' => 'strip', 'category_id' => $supply->id],
            ['name' => 'Syrup Entacyd Plus', 'strength' => null, 'dosage_form' => 'syrup', 'unit' => 'ml', 'category_id' => $syrup->id],
            ['name' => 'Volinac', 'strength' => null, 'dosage_form' => 'gel', 'unit' => 'tube', 'category_id' => $topical->id],
            ['name' => 'Optimox', 'strength' => null, 'dosage_form' => 'drop', 'unit' => 'bottle', 'category_id' => $drop->id],
            ['name' => 'Savlon', 'strength' => 'Liquid', 'dosage_form' => 'liquid', 'unit' => 'bottle', 'category_id' => $liquid->id],
            ['name' => 'Savlon', 'strength' => 'Cream', 'dosage_form' => 'cream', 'unit' => 'tube', 'category_id' => $topical->id],
            ['name' => 'Hexisol', 'strength' => null, 'dosage_form' => 'liquid', 'unit' => 'bottle', 'category_id' => $liquid->id],
            ['name' => 'Burna', 'strength' => 'Cream', 'dosage_form' => 'cream', 'unit' => 'tube', 'category_id' => $topical->id],
            ['name' => 'Cotton', 'strength' => null, 'dosage_form' => 'supply', 'unit' => 'packet', 'category_id' => $supply->id],
            ['name' => 'Surgical Gauze', 'strength' => null, 'dosage_form' => 'supply', 'unit' => 'roll', 'category_id' => $supply->id],
            ['name' => 'Roll Bandage', 'strength' => '2-4 inch', 'dosage_form' => 'supply', 'unit' => 'roll', 'category_id' => $supply->id],
            ['name' => 'Trego', 'strength' => 'Cream', 'dosage_form' => 'cream', 'unit' => 'tube', 'category_id' => $topical->id],
            ['name' => 'Clofenac', 'strength' => 'Gel', 'dosage_form' => 'gel', 'unit' => 'tube', 'category_id' => $topical->id],
            ['name' => 'Nix', 'strength' => null, 'dosage_form' => 'supply', 'unit' => 'piece', 'category_id' => $supply->id],
        ];

        foreach ($items as $item) {
            Medicine::firstOrCreate(
                ['name' => $item['name'], 'strength' => $item['strength'], 'dosage_form' => $item['dosage_form']],
                [
                    'sku' => Str::upper(Str::slug(Str::substr($item['name'], 0, 10), '')).'-'.random_int(1000, 9999),
                    'medicine_category_id' => $item['category_id'],
                    'base_unit' => $item['unit'],
                    'purchase_unit' => $item['unit'],
                    'issue_unit' => $item['unit'],
                    'units_per_purchase_pack' => 1,
                    'minimum_stock_level' => 0,
                    'reorder_level' => 10,
                    'status' => 'active',
                    'remarks' => 'Seeded from the legacy workbook Medicine List. Confirm base unit, pack size, and reorder level with the medical room officer.',
                ]
            );
        }
    }
}
