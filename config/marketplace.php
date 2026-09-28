<?php

return [

    // Added on top of the publisher's price. 0.20 = the buyer pays 120% of the publisher price.
    'commission_rate' => (float) env('MARKETPLACE_COMMISSION_RATE', 0.20),

    'min_topup_dzd' => 1000,
    'min_payout_dzd' => 5000,

    // Publisher must accept within this many days, or the order is cancelled and refunded.
    'accept_within_days' => 3,

    // After publication the buyer has this many days to validate or dispute, then it auto-completes.
    'auto_validate_days' => 5,

    // How long a completed link is monitored.
    'monitor_months' => 12,

    'categories' => [
        'business', 'finance', 'tech', 'ecommerce', 'health', 'education',
        'travel', 'auto', 'real-estate', 'lifestyle', 'sport', 'news',
    ],

    'chargily' => [
        'mode' => env('CHARGILY_MODE', 'test'), // test | live
        'secret_key' => env('CHARGILY_SECRET_KEY'),
        'base_url' => [
            'test' => 'https://pay.chargily.net/test/api/v2',
            'live' => 'https://pay.chargily.net/api/v2',
        ],
    ],

    // Without a Chargily key, local and testing environments credit top-ups instantly.
    'fake_payments' => env('MARKETPLACE_FAKE_PAYMENTS', false),

];
