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

    'mailersend' => [
        'api_key' => env('MAILERSEND_API_KEY'),
        'api_url' => env('MAILERSEND_API_URL', 'https://api.mailersend.com/v1'),
        'from_email' => env('MAILERSEND_FROM_EMAIL', 'admin@zawajafrica.com.ng'),
        'from_name' => env('MAILERSEND_FROM_NAME', 'ZawajAfrica'),
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

    'monnify' => [
        'api_key' => env('MONNIFY_API_KEY'),
        'secret_key' => env('MONNIFY_SECRET_KEY'),
        'contract_code' => env('MONNIFY_CONTRACT_CODE'),
        'base_url' => env('MONNIFY_BASE_URL', 'https://sandbox-api.monnify.com'),
        'enabled' => env('MONNIFY_ENABLED', false),
    ],

    'openai' => [
        'enabled' => env('OPENAI_ENABLED', true),
        'api_key' => env('OPENAI_API_KEY'),
        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
        'top_p' => (float) env('OPENAI_TOP_P', 1.0),
        'frequency_penalty' => (float) env('OPENAI_FREQUENCY_PENALTY', 0.0),
        'presence_penalty' => (float) env('OPENAI_PRESENCE_PENALTY', 0.0),
        'max_history' => (int) env('OPENAI_MAX_HISTORY', 20),
        'rate_limit' => (int) env('OPENAI_RATE_LIMIT', 60),
        'system_prompt' => env('OPENAI_SYSTEM_PROMPT', 'You are an intelligent, warm-hearted, professional female assistant working for ZawajAfrica — a premium matchmaking platform exclusively for African Muslims in Africa and the diaspora. You serve as both a companion and guide to users navigating love, compatibility, and values. From 12 noon to 12 midnight, you are Fatima. From 12 midnight to 12 noon, you are Firdaus. When starting a new conversation with a user for the first time, start the conversation with "Salam alaikum" but no need to start every message with it after the start of the conversation and introduce yourself using the appropriate name based on current time, then ask politely: "Which language would you prefer to chat in? I understand Hausa, Yoruba, Igbo, Arabic, Swahili, and French, depending on users region." For subsequent messages in the same conversation, respond naturally without repeating the greeting. Your purpose is to help African Muslims meet for halal, purpose-driven marriages and connect users by compatibility, values, and clear intentions. Your vision is to be the most trusted, faith-based, Africa-centric matchmaking platform globally. Your mission is to guide African Muslims into meaningful unions through safe, verified, value-oriented processes. You have access to our therapist listings and can recommend specific therapists based on user needs. When recommending therapists, mention their names, specializations, experience, and rates. You can direct users to book appointments by telling them to visit the therapists section on the platform.'),
    ],

    'zoho_mail' => [
        'enabled' => env('ZOHO_MAIL_ENABLED', false),
        'from_address' => env('ZOHO_MAIL_FROM_ADDRESS'),
        'from_name' => env('ZOHO_MAIL_FROM_NAME', 'ZawajAfrica'),
        
        // Zoho OAuth Configuration
        'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
        'client_id' => env('ZOHO_CLIENT_ID'),
        'client_secret' => env('ZOHO_CLIENT_SECRET'),
        'account_id' => env('ZOHO_MAIL_ACCOUNT_ID'),
        
        // Multiple email addresses for different purposes
        'addresses' => [
            'support' => [
                'address' => env('ZOHO_MAIL_SUPPORT_ADDRESS', 'support@zawajafrica.com.ng'),
                'name' => env('ZOHO_MAIL_SUPPORT_NAME', 'ZawajAfrica Support Team'),
            ],
            'admin' => [
                'address' => env('ZOHO_MAIL_ADMIN_ADDRESS', 'admin@zawajafrica.com.ng'),
                'name' => env('ZOHO_MAIL_ADMIN_NAME', 'ZawajAfrica Admin'),
            ],
            'therapist' => [
                'address' => env('ZOHO_MAIL_THERAPIST_ADDRESS', 'support@zawajafrica.com.ng'),
                'name' => env('ZOHO_MAIL_THERAPIST_NAME', 'ZawajAfrica Therapy Services'),
            ],
            'noreply' => [
                'address' => env('ZOHO_MAIL_NOREPLY_ADDRESS', 'noreply@zawajafrica.com.ng'),
                'name' => env('ZOHO_MAIL_NOREPLY_NAME', 'ZawajAfrica'),
            ],
        ],
    ],

    'zoho_bookings' => [
        'enabled' => env('ZOHO_BOOKINGS_ENABLED', false),
        'client_id' => env('ZOHO_BOOKINGS_CLIENT_ID'),
        'client_secret' => env('ZOHO_BOOKINGS_CLIENT_SECRET'),
        'refresh_token' => env('ZOHO_BOOKINGS_REFRESH_TOKEN'),
        'organization_id' => env('ZOHO_BOOKINGS_ORGANIZATION_ID'),
        'data_center' => env('ZOHO_BOOKINGS_DATA_CENTER', 'com'),
        'webhook_secret' => env('ZOHO_BOOKINGS_WEBHOOK_SECRET'),
    ],

    'zoho_campaign' => [
        'enabled' => env('ZOHO_CAMPAIGN_ENABLED', false),
        'client_id' => env('ZOHO_CAMPAIGN_CLIENT_ID'),
        'client_secret' => env('ZOHO_CAMPAIGN_CLIENT_SECRET'),
        'refresh_token' => env('ZOHO_CAMPAIGN_REFRESH_TOKEN'),
        'from_email' => env('ZOHO_CAMPAIGN_FROM_EMAIL', 'admin@zawajafrica.com.ng'),
        'from_name' => env('ZOHO_CAMPAIGN_FROM_NAME', 'ZawajAfrica'),
        'data_center' => env('ZOHO_CAMPAIGN_DATA_CENTER', 'com'),
        'redirect_uri' => env('ZOHO_CAMPAIGN_REDIRECT_URI', 'https://zawajafrica.com.ng/zoho-callback'),
    ],

];
