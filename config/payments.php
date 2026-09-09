<?php

return [
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'paystack'),
    'currency' => env('PAYMENT_DEFAULT_CURRENCY', env('PRODUCT_DEFAULT_CURRENCY', 'NGN')),
    'settings_cache_ttl' => (int) env('PAYMENT_SETTINGS_CACHE_TTL', 300),
    'gateways' => [
        'stripe' => [
            'enabled' => (bool) env('STRIPE_ENABLED', false),
            'mode' => env('STRIPE_MODE', 'sandbox'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'base_url' => env('STRIPE_BASE_URL', 'https://api.stripe.com'),
        ],
        'paypal' => [
            'enabled' => (bool) env('PAYPAL_ENABLED', false),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
            'sandbox_base_url' => env('PAYPAL_SANDBOX_BASE_URL', 'https://api-m.sandbox.paypal.com'),
            'live_base_url' => env('PAYPAL_LIVE_BASE_URL', 'https://api-m.paypal.com'),
        ],
        'paystack' => [
            'enabled' => (bool) env('PAYSTACK_ENABLED', false),
            'mode' => env('PAYSTACK_MODE', 'sandbox'),
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        ],
    ],
];
