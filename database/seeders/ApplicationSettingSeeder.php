<?php

namespace Database\Seeders;

use App\Models\ApplicationSetting;
use Illuminate\Database\Seeder;

class ApplicationSettingSeeder extends Seeder
{
    /**
     * System-wide defaults (not per factory). The first three are the keys
     * the Settings screen edits; the rest document the policies the app is
     * built on so they are visible in one place.
     */
    public function run(): void
    {
        $settings = [
            ['low_stock_default_threshold_days', '30', 'int', 'Default number of days of stock cover below which a medicine is flagged as low.'],
            ['expiry_alert_days', '90', 'int', 'Batches expiring within this many days appear on the Expiry Management screen.'],
            ['currency_symbol', '৳', 'string', 'Currency symbol shown next to amounts.'],
            ['currency_code', 'BDT', 'string', 'Currency used for all costs and purchase prices.'],
            ['date_format', 'd-m-Y', 'string', 'Display format for dates (DD-MM-YYYY).'],
            ['timezone', 'Asia/Dhaka', 'string', 'Application timezone.'],
            ['valuation_policy', 'FIFO', 'string', 'Inventory valuation: oldest-expiry batch is issued first and costed at its own price.'],
        ];

        foreach ($settings as [$key, $value, $type, $description]) {
            ApplicationSetting::set($key, $value, $type, $description);
        }
    }
}
