<?php

// Application-specific business settings for the Factory Medicine Inventory system.
return [
    'currency_code' => env('CURRENCY_CODE', 'BDT'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '৳'),
    'date_format' => env('DATE_FORMAT', 'd-m-Y'),

    // Inventory valuation policy. FIFO is implemented; keep this documented
    // and do not change without re-costing existing batches.
    'valuation_policy' => 'FIFO',

    'low_stock_default_threshold_days' => (int) env('LOW_STOCK_DEFAULT_THRESHOLD_DAYS', 30),
    'expiry_alert_windows_days' => [30, 60, 90],

    'roles' => [
        'admin' => 'Admin',
        'store_officer' => 'Medical Room / Store Officer',
        'accounts' => 'Accounts',
        'management' => 'Management / Director',
    ],
];
