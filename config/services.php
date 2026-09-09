<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'mailersend' => [
        'api_key' => env('MAILERSEND_API_KEY'),
        'api_url' => env('MAILERSEND_API_URL', 'https://api.mailersend.com/v1'),
        'from_email' => env('MAILERSEND_FROM_EMAIL', 'noreply@example.com'),
        'from_name' => env('MAILERSEND_FROM_NAME', env('APP_NAME', 'ZawajAfrica')),
        'enabled' => env('MAILERSEND_ENABLED', false),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    ],

    'google' => [
        'pagespeed_key' => env('GOOGLE_PAGESPEED_KEY'),
    ],
];
