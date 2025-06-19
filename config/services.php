<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

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

    'openai' => [
        'enabled' => env('OPENAI_ENABLED', true),
        'api_key' => env('OPENAI_API_KEY'),
        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-nano'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
        'top_p' => (float) env('OPENAI_TOP_P', 1.0),
        'frequency_penalty' => (float) env('OPENAI_FREQUENCY_PENALTY', 0.0),
        'presence_penalty' => (float) env('OPENAI_PRESENCE_PENALTY', 0.0),
        'max_history' => (int) env('OPENAI_MAX_HISTORY', 20),
        'rate_limit' => (int) env('OPENAI_RATE_LIMIT', 60),
        'system_prompt' => env('OPENAI_SYSTEM_PROMPT', 'You are an AI assistant for ZawajAfrica, a matrimonial and therapy platform. You help users with relationship advice, profile optimization, cultural guidance, and platform navigation. Be respectful, culturally sensitive, and supportive.'),
    ],

];
