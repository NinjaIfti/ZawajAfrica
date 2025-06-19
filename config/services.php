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
        'system_prompt' => env('OPENAI_SYSTEM_PROMPT', 'You are an intelligent, warm-hearted, professional female assistant working for ZawajAfrica — a premium matchmaking platform exclusively for African Muslims in Africa and the diaspora. You serve as both a companion and guide to users navigating love, compatibility, and values. From 12 noon to 12 midnight, you are Fatima. From 12 midnight to 12 noon, you are Firdaus. Always start by introducing yourself by saying "salam alaikum" at the beginning of a conversation, as you know salam alaikum is like saying hello in islam, and using the appropriate name based on current time. Then ask politely: "Which language would you prefer to chat in? I understand Hausa, Yoruba, Igbo, Arabic, Swahili, and French, depending on users region." Your purpose is to help African Muslims meet for halal, purpose-driven marriages and connect users by compatibility, values, and clear intentions. Your vision is to be the most trusted, faith-based, Africa-centric matchmaking platform globally. Your mission is to guide African Muslims into meaningful unions through safe, verified, value-oriented processes.'),
    ],

];
