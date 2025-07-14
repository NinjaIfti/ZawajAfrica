<?php

return [
    // Core configuration
    'enabled' => env('ADSTERRA_ENABLED', true),
    'test_mode' => env('ADSTERRA_TEST_MODE', false),
    
    // Adsterra configuration - REQUIRED for production
    'script_url' => env('ADSTERRA_SCRIPT_URL', '//pl27099042.profitableratecpm.com/40/25/2a/40252a1397d95eb269852aea67a5c58f.js'),
    'publisher_id' => env('ADSTERRA_PUBLISHER_ID', '40252a1397d95eb269852aea67a5c58f'),
    
    // Display settings
    'display' => [
        'show_on_mobile' => env('ADSTERRA_SHOW_ON_MOBILE', true),
        'dashboard_feed_enabled' => env('ADSTERRA_DASHBOARD_FEED_ENABLED', true),
        'sidebar_ads_enabled' => env('ADSTERRA_SIDEBAR_ADS_ENABLED', true),
        'profile_frequency' => env('ADSTERRA_PROFILE_FREQUENCY', 10),
        'max_ads_per_page' => env('ADSTERRA_MAX_ADS_PER_PAGE', 5),
        'lazy_loading' => env('ADSTERRA_LAZY_LOADING', true),
    ],
    
    // Ad zones - Configure these in your Adsterra dashboard
    'ad_zones' => [
        'banner' => env('ADSTERRA_BANNER_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'popup' => env('ADSTERRA_POPUP_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'push' => env('ADSTERRA_PUSH_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'native' => env('ADSTERRA_NATIVE_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'video' => env('ADSTERRA_VIDEO_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'interstitial' => env('ADSTERRA_INTERSTITIAL_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'feed' => env('ADSTERRA_FEED_ZONE', '40252a1397d95eb269852aea67a5c58f'),
        'sidebar' => env('ADSTERRA_SIDEBAR_ZONE', '40252a1397d95eb269852aea67a5c58f'),
    ],
    
    // Targeting configuration
    'targeting' => [
        'allowed_countries' => array_filter(explode(',', env('ADSTERRA_ALLOWED_COUNTRIES', 'NG,US,GB,CA,AU'))),
        'blocked_countries' => array_filter(explode(',', env('ADSTERRA_BLOCKED_COUNTRIES', ''))),
        'allowed_device_types' => array_filter(explode(',', env('ADSTERRA_ALLOWED_DEVICES', 'mobile,desktop,tablet'))),
        'min_screen_width' => env('ADSTERRA_MIN_SCREEN_WIDTH', 320),
    ],
    
    // Privacy and compliance
    'privacy' => [
        'gdpr_enabled' => env('ADSTERRA_GDPR_ENABLED', true),
        'cookie_consent' => env('ADSTERRA_COOKIE_CONSENT_REQUIRED', false),
        'ccpa_enabled' => env('ADSTERRA_CCPA_ENABLED', true),
        'coppa_enabled' => env('ADSTERRA_COPPA_ENABLED', false),
        'consent_timeout' => env('ADSTERRA_CONSENT_TIMEOUT', 30000), // 30 seconds
    ],
    
    // Performance settings
    'performance' => [
        'timeout' => env('ADSTERRA_TIMEOUT', 5000),
        'async_loading' => env('ADSTERRA_ASYNC_LOADING', true),
        'preload_enabled' => env('ADSTERRA_PRELOAD_ENABLED', true),
        'cache_ttl' => env('ADSTERRA_CACHE_TTL', 3600), // 1 hour
        'retry_attempts' => env('ADSTERRA_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('ADSTERRA_RETRY_DELAY', 1000), // 1 second
    ],
    
    // Analytics and monitoring
    'analytics' => [
        'tracking_enabled' => env('ADSTERRA_ANALYTICS_TRACKING', true),
        'impressions_tracking' => env('ADSTERRA_IMPRESSIONS_TRACKING', true),
        'click_tracking' => env('ADSTERRA_CLICK_TRACKING', true),
        'revenue_tracking' => env('ADSTERRA_REVENUE_TRACKING', true),
        'error_tracking' => env('ADSTERRA_ERROR_TRACKING', true),
        'performance_tracking' => env('ADSTERRA_PERFORMANCE_TRACKING', true),
    ],
    
    // Security settings
    'security' => [
        'csp_enabled' => env('ADSTERRA_CSP_ENABLED', true),
        'allowed_domains' => [
            'adsterra.com',
            'adsterra.net',
            'adsterranetwork.com',
            'pl27099042.profitableratecpm.com'
        ],
        'validate_scripts' => env('ADSTERRA_VALIDATE_SCRIPTS', true),
        'block_malicious_ads' => env('ADSTERRA_BLOCK_MALICIOUS', true),
        'rate_limiting' => env('ADSTERRA_RATE_LIMITING', true),
        'max_requests_per_minute' => env('ADSTERRA_MAX_REQUESTS_PER_MINUTE', 60),
    ],
    
    // Debug and development
    'debug' => [
        'enabled' => env('ADSTERRA_DEBUG_MODE', true),
        'console_logging' => env('ADSTERRA_CONSOLE_LOGGING', true),
        'detailed_errors' => env('ADSTERRA_DETAILED_ERRORS', true),
        'test_ads' => env('ADSTERRA_TEST_ADS', false),
    ],
    
    // Page restrictions
    'restricted_pages' => [
        'payment', 'subscription', 'verification', 'admin', 'settings',
        'login', 'register', 'mobile-login', 'password/reset', 'password/confirm', 
        'email/verify', 'two-factor-challenge', 'forgot-password', 'verify-email'
    ],
    
    // Fallback and error handling
    'fallback' => [
        'enabled' => env('ADSTERRA_FALLBACK_ENABLED', true),
        'fallback_message' => env('ADSTERRA_FALLBACK_MESSAGE', 'Advertisement unavailable'),
        'show_fallback' => env('ADSTERRA_SHOW_FALLBACK', false),
        'hide_on_error' => env('ADSTERRA_HIDE_ON_ERROR', true),
    ],
    
    // Quality settings
    'quality' => [
        'block_adult_content' => env('ADSTERRA_BLOCK_ADULT', true),
        'block_gambling' => env('ADSTERRA_BLOCK_GAMBLING', true),
        'block_violence' => env('ADSTERRA_BLOCK_VIOLENCE', true),
        'family_safe' => env('ADSTERRA_FAMILY_SAFE', true),
    ],
]; 