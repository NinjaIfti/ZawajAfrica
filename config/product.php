<?php

$csv = static fn (string $key, string $default): array => array_values(array_filter(array_map(
    static fn (string $value): string => trim($value),
    explode(',', (string) env($key, $default))
)));

return [
    'name' => env('PRODUCT_NAME', env('APP_NAME', 'ZawajAfrica')),
    'tagline' => env('PRODUCT_TAGLINE', 'Meaningful connections, guided by faith.'),

    'logos' => [
        'primary' => env('PRODUCT_LOGO', '/images/logo.png'),
        'compact' => env('PRODUCT_LOGO_COMPACT', '/images/fav.png'),
        'favicon' => env('PRODUCT_FAVICON', '/images/fav.png'),
    ],

    'colors' => [
        'primary' => env('PRODUCT_COLOR_PRIMARY', '#654396'),
        'secondary' => env('PRODUCT_COLOR_SECONDARY', '#4B5563'),
        'accent' => env('PRODUCT_COLOR_ACCENT', '#F59E0B'),
    ],

    'locale' => env('PRODUCT_LOCALE', env('APP_LOCALE', 'en')),
    'timezone' => env('PRODUCT_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    'defaults' => [
        'country' => env('PRODUCT_DEFAULT_COUNTRY', 'NG'),
        'currency' => env('PRODUCT_DEFAULT_CURRENCY', 'NGN'),
    ],

    'supported' => [
        'countries' => $csv('PRODUCT_SUPPORTED_COUNTRIES', 'NG,GH,KE,ZA,GB,US,CA'),
        'currencies' => $csv('PRODUCT_SUPPORTED_CURRENCIES', 'NGN,USD,GBP,EUR,GHS,KES,ZAR'),
    ],

    'legal' => [
        'terms' => env('PRODUCT_TERMS_URL', '/terms'),
        'privacy' => env('PRODUCT_PRIVACY_URL', '/privacy'),
        'cookies' => env('PRODUCT_COOKIES_URL', '/cookies'),
        'community_guidelines' => env('PRODUCT_COMMUNITY_GUIDELINES_URL', '/community-guidelines'),
        'contact' => env('PRODUCT_CONTACT_URL', '/contact'),
    ],

    'features' => [
        'matching' => (bool) env('FEATURE_MATCHING_ENABLED', true),
        'messaging' => (bool) env('FEATURE_MESSAGING_ENABLED', true),
        'verification' => (bool) env('FEATURE_VERIFICATION_ENABLED', true),
        'kyc' => (bool) env('FEATURE_KYC_ENABLED', true),
        'subscriptions' => (bool) env('FEATURE_SUBSCRIPTIONS_ENABLED', true),
        'therapists' => (bool) env('FEATURE_THERAPISTS_ENABLED', true),
        'ai' => (bool) env('FEATURE_AI_ENABLED', true),
        'advertising' => (bool) env('FEATURE_ADVERTISING_ENABLED', true),
        'zoho' => (bool) env('FEATURE_ZOHO_ENABLED', true),
    ],

    // Provider names are public capability flags. Credentials remain in config/services.php.
    'payment_providers' => $csv('PAYMENT_PROVIDERS_ENABLED', 'paystack,monnify,manual'),

    // Disabled by default. This is migration compatibility only, not the primary authorization model.
    'legacy_admin' => [
        'enabled' => (bool) env('LEGACY_ADMIN_FALLBACK_ENABLED', false),
        'email' => env('LEGACY_ADMIN_EMAIL'),
    ],
];
