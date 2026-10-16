<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\MedicineBatch;
use App\Services\StockService;
use Database\Seeders\Concerns\SeedsPerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OpeningStockSeeder extends Seeder
{
    use SeedsPerFactory;

    /**
     * SAMPLE figures only. Quantities are in each medicine's base unit and
     * costs are illustrative BDT per base unit — replace them with real
     * opening balances and prices before using this for real stock.
     * Keyed "Name|Strength" (empty strength for medicines without one).
     */
    public static function catalog(): array
    {
        return [
            'Napa|500mg' => ['qty' => 600, 'cost' => 1.20, 'exp' => '+20 months'],
            'ORS|' => ['qty' => 300, 'cost' => 5.00, 'exp' => '+24 months'],
            'Entacyd Plus|400mg' => ['qty' => 400, 'cost' => 1.50, 'exp' => '+18 months'],
            'Alzine|50mg' => ['qty' => 200, 'cost' => 3.00, 'exp' => '+55 days'],   // expires soon
            'Omidon|10mg' => ['qty' => 300, 'cost' => 2.00, 'exp' => '+25 days'],   // expires soon
            'Fenadin|120mg' => ['qty' => 150, 'cost' => 6.00, 'exp' => '+14 months'],
            'Viodin|' => ['qty' => 100, 'cost' => 2.00, 'exp' => '+16 months'],
            'Amodis DS|400mg' => ['qty' => 200, 'cost' => 3.00, 'exp' => '+20 months'],
            'Rolac|10mg' => ['qty' => 200, 'cost' => 5.00, 'exp' => '+15 months'],
            'Green Strip|' => ['qty' => 40, 'cost' => 10.00, 'exp' => '+30 months'],
            'Syrup Entacyd Plus|' => ['qty' => 1000, 'cost' => 1.20, 'exp' => '+12 months'],
            'Volinac|' => ['qty' => 15, 'cost' => 60.00, 'exp' => '+22 months'],
            'Optimox|' => ['qty' => 12, 'cost' => 90.00, 'exp' => '+80 days'],
            'Savlon|Liquid' => ['qty' => 20, 'cost' => 120.00, 'exp' => '+30 months'],
            'Savlon|Cream' => ['qty' => 15, 'cost' => 80.00, 'exp' => '+24 months'],
            'Hexisol|' => ['qty' => 10, 'cost' => 110.00, 'exp' => '+26 months'],
            'Burna|Cream' => ['qty' => 15, 'cost' => 70.00, 'exp' => '+20 months'],
            'Cotton|' => ['qty' => 12, 'cost' => 60.00, 'exp' => '+36 months'],
            'Surgical Gauze|' => ['qty' => 8, 'cost' => 45.00, 'exp' => '+36 months'],  // already low
            'Roll Bandage|2-4 inch' => ['qty' => 25, 'cost' => 25.00, 'exp' => '+48 months'],
            'Trego|Cream' => ['qty' => 12, 'cost' => 85.00, 'exp' => '+22 months'],
            'Clofenac|Gel' => ['qty' => 12, 'cost' => 55.00, 'exp' => '+20 months'],
            'Nix|' => ['qty' => 100, 'cost' => 15.00, 'exp' => '+30 months'],
        ];
    }

    public static function key(string $name, ?string $strength): string
    {
        return $name.'|'.($strength ?? '');
    }

    public function run(): void
    {
        $realToday = Carbon::today();

        $this->eachFactory(function (Factory $factory) use ($realToday) {
            if (MedicineBatch::exists()) {
                return; // opening stock already loaded for this factory
            }

            $stock = app(StockService::class);
            $this->actingAs($factory, 'store_officer');

            $openingDay = $realToday->copy()->subDays(60);
            Carbon::setTestNow($openingDay->copy()->setTime(9, 0));

            foreach (self::catalog() as $key => $row) {
                [$name, $strength] = explode('|', $key);
                $medicine = $this->medicine($name, $strength === '' ? null : $strength);

                $batch = $stock->openingBalance(
                    $medicine,
                    (float) $row['qty'],
                    'OPEN-'.$medicine->sku,
                    $openingDay->toDateString(),
                    costUnavailable: false,
                    unitCost: $row['cost'],
                );
                $batch->update([
                    'expiry_date' => $realToday->copy()->modify($row['exp'])->toDateString(),
                    'remarks' => 'Sample opening balance.',
                ]);

                $medicine->update(['default_purchase_price' => $row['cost']]);
            }

            // Two extra lots that exist only to demonstrate write-offs on the
            // Adjustments screen (see SampleActivitySeeder).
            $hexisol = $this->medicine('Hexisol');
            $stock->openingBalance($hexisol, 3, 'HEX-OLD', $openingDay->toDateString(), false, 105.00)
                ->update(['expiry_date' => $realToday->copy()->addDays(40)->toDateString(), 'remarks' => 'Sample lot: bottles found cracked, written off as damaged.']);

            $syrup = $this->medicine('Syrup Entacyd Plus');
            $stock->openingBalance($syrup, 120, 'SYR-EXP', $openingDay->toDateString(), false, 1.10)
                ->update(['expiry_date' => $realToday->copy()->subDays(10)->toDateString(), 'remarks' => 'Sample lot: passed its expiry date, written off.']);
        });
    }
}
