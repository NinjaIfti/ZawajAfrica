<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateZohoTokens extends Command
{
    protected $signature = 'zoho:generate-tokens';
    protected $description = 'Get instructions and URLs to generate new Zoho Campaign tokens';

    public function handle()
    {
        $this->info('🔧 Zoho Campaign Token Generation Guide');
        $this->line('');
        
        $clientId = config('services.zoho_campaign.client_id');
        $clientSecret = config('services.zoho_campaign.client_secret');
        
        if (!$clientId || !$clientSecret) {
            $this->error('❌ Missing Zoho Campaign client credentials in your .env file.');
            $this->info('Please add:');
            $this->info('ZOHO_CAMPAIGN_CLIENT_ID=your_client_id');
            $this->info('ZOHO_CAMPAIGN_CLIENT_SECRET=your_client_secret');
            return;
        }
        
        $this->info('✅ Found client credentials');
        $this->info("Client ID: {$clientId}");
        $this->line('');
        
        $this->info('📋 Step 1: Get Authorization Code');
        $this->line('Visit this URL in your browser:');
        $this->line('');
        
        $redirectUri = config('services.zoho_campaign.redirect_uri', 'https://zawajafrica.com.ng/zoho-callback');
        
        $authUrl = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => 'ZohoCampaigns.contact.READ,ZohoCampaigns.contact.CREATE,ZohoCampaigns.contact.UPDATE,ZohoCampaigns.campaign.READ,ZohoCampaigns.campaign.CREATE,ZohoCampaigns.campaign.UPDATE',
            'redirect_uri' => $redirectUri,
            'access_type' => 'offline'
        ]);
        
        $this->comment($authUrl);
        $this->line('');
        
                    $this->info('📋 Step 2: After authorization, get the code from URL');
            $this->info("The URL will look like: {$redirectUri}?code=YOUR_CODE");
        $this->line('');
        
        $code = $this->ask('Paste the authorization code here');
        
        if ($code) {
            $this->info('📋 Step 3: Exchange code for tokens');
            $this->info('Run this curl command:');
            $this->line('');
            
            $curlCommand = "curl -X POST https://accounts.zoho.com/oauth/v2/token \\" . PHP_EOL .
                          "  -d 'grant_type=authorization_code' \\" . PHP_EOL .
                          "  -d 'client_id={$clientId}' \\" . PHP_EOL .
                          "  -d 'client_secret={$clientSecret}' \\" . PHP_EOL .
                          "  -d 'redirect_uri={$redirectUri}' \\" . PHP_EOL .
                          "  -d 'code={$code}'";
            
            $this->comment($curlCommand);
            $this->line('');
            
            $this->info('📋 Step 4: Update your .env file');
            $this->info('From the response, copy the refresh_token and update:');
            $this->info('ZOHO_CAMPAIGN_REFRESH_TOKEN=your_new_refresh_token');
            $this->line('');
            
            $this->info('📋 Step 5: Test the connection');
            $this->info('Run: php artisan zoho:test-connection');
        }
        
        $this->line('');
        $this->info('💡 Alternative: Use Zoho Developer Console');
        $this->info('1. Go to https://api-console.zoho.com/');
        $this->info('2. Go to your Zoho Campaign app');
        $this->info('3. Generate tokens from the console');
        $this->info('4. Update your .env with the new refresh token');
    }
} 