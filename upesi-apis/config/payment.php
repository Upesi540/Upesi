<?php
// config/payment.php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'fedapay'),

    'gateways' => [
        'fedapay' => [
            'enabled' => true,
            'secret_key' => env('FEDAPAY_SECRET_KEY'),
            'public_key' => env('FEDAPAY_PUBLIC_KEY'),
            'webhook_secret' => env('FEDAPAY_WEBHOOK_SECRET'),
            'environment' => env('FEDAPAY_ENVIRONMENT', 'sandbox'),
            'currencies' => ['XOF'],
            'min_withdrawal' => 53500,
            'max_withdrawal' => 10000000
        ],
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', false),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'currencies' => ['USD', 'EUR', 'GBP']
        ]
    ],

    'withdrawal' => [
        'min_amount' => env('WITHDRAWAL_MIN_AMOUNT', 53500),
        'max_amount' => env('WITHDRAWAL_MAX_AMOUNT', 1000000),
        'daily_limit_per_user' => env('WITHDRAWAL_DAILY_LIMIT', 3000000)
    ]
];
