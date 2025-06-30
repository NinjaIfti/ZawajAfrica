<?php

return [
    'enabled' => env('ADSENSE_ENABLED', true),
    'test_mode' => env('ADSENSE_TEST_MODE', false),
    
    'publisher_id' => env('ADSENSE_TEST_MODE', false) 
        ? env('ADSENSE_TEST_PUBLISHER_ID', 'ca-pub-3940256099942544')
        : env('ADSENSE_PUBLISHER_ID'),
    
    'auto_ads' => [
        'enabled' => env('ADSENSE_AUTO_ADS_ENABLED', true),
        'page_level' => env('ADSENSE_PAGE_LEVEL_ADS', true),
        'anchor_ads' => env('ADSENSE_ANCHOR_ADS', true),
        'vignette_ads' => env('ADSENSE_VIGNETTE_ADS', true),
    ],
    
    'display' => [
        'lazy_loading' => env('ADSENSE_LAZY_LOADING', true),
        'profile_frequency' => env('ADSENSE_PROFILE_FREQUENCY', 10),
        'show_on_mobile' => env('ADSENSE_SHOW_ON_MOBILE', true),
        'dashboard_feed_enabled' => env('ADSENSE_DASHBOARD_FEED_ENABLED', true),
        'sidebar_ads_enabled' => env('ADSENSE_SIDEBAR_ADS_ENABLED', true),
    ],
    
    'ad_slots' => [
        'dashboard_feed' => '5106076698', // Fluid format dashboard feed ad
        'display_auto' => '8367483099',   // Auto format display ad
        'main_auto' => env('ADSENSE_MAIN_AUTO_SLOT', ''), // For future use
    ],
    
    'targeting' => [
        'allowed_countries' => array_filter(explode(',', env('ADSENSE_ALLOWED_COUNTRIES', 'NG,US,GB,CA,AU'))),
        'blocked_countries' => array_filter(explode(',', env('ADSENSE_BLOCKED_COUNTRIES', ''))),
        'block_sensitive' => env('ADSENSE_SENSITIVE_CATEGORIES_BLOCKED', true),
    ],
    
    'privacy' => [
        'gdpr_enabled' => env('ADSENSE_GDPR_ENABLED', true),
        'cookie_consent' => env('ADSENSE_COOKIE_CONSENT_REQUIRED', true),
        'personalized_ads' => env('ADSENSE_PERSONALIZED_ADS', true),
        'data_processing_consent' => env('ADSENSE_DATA_PROCESSING_CONSENT', true),
    ],
    
    'performance' => [
        'timeout' => env('ADSENSE_TIMEOUT', 5000),
        'retry_attempts' => env('ADSENSE_RETRY_ATTEMPTS', 3),
        'async_loading' => env('ADSENSE_ASYNC_LOADING', true),
    ],
    
    'analytics' => [
        'tracking_enabled' => env('ADSENSE_ANALYTICS_TRACKING', true),
        'revenue_tracking' => env('ADSENSE_REVENUE_TRACKING', true),
        'impressions_tracking' => env('ADSENSE_IMPRESSIONS_TRACKING', true),
    ],
    
    'debug' => [
        'enabled' => env('ADSENSE_DEBUG_MODE', false),
        'console_logging' => env('ADSENSE_CONSOLE_LOGGING', false),
        'sandbox' => env('ADSENSE_SANDBOX_MODE', false),
    ],
    
    'restricted_pages' => [
        'payment', 'subscription', 'verification', 'admin'
    ],
]; 