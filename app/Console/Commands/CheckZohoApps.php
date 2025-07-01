<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckZohoApps extends Command
{
    protected $signature = 'zoho:check-apps';
    protected $description = 'Check which Zoho app has Campaign permissions';

    public function handle()
    {
        $this->info('🔍 Checking Zoho App Configuration...');
        $this->line('');
        
        $currentClientId = config('services.zoho_campaign.client_id');
        $currentClientSecret = config('services.zoho_campaign.client_secret');
        
        $this->info('📋 Current Configuration:');
        $this->info("Client ID: {$currentClientId}");
        $this->info("Client Secret: " . substr($currentClientSecret, 0, 10) . "...");
        $this->line('');
        
        $this->info('🔧 To fix the token mismatch:');
        $this->line('');
        
        $this->info('Option 1: Use Current App (Client ID: ' . substr($currentClientId, 0, 20) . '...)');
        $this->info('1. Go to https://api-console.zoho.com/');
        $this->info('2. Find the app with Client ID: ' . $currentClientId);
        $this->info('3. Go to "Client Secret" tab');
        $this->info('4. Click "Generate Token"');
        $this->info('5. Select Campaign scopes:');
        $this->info('   - ZohoCampaigns.contact.READ');
        $this->info('   - ZohoCampaigns.contact.CREATE');
        $this->info('   - ZohoCampaigns.contact.UPDATE');
        $this->info('   - ZohoCampaigns.campaign.READ');
        $this->info('   - ZohoCampaigns.campaign.CREATE');
        $this->info('   - ZohoCampaigns.campaign.UPDATE');
        $this->info('6. Copy the refresh_token');
        $this->info('7. Update your .env: ZOHO_CAMPAIGN_REFRESH_TOKEN=new_token');
        $this->line('');
        
        $this->info('Option 2: Use New App');
        $this->info('1. Update your .env file with the new app credentials:');
        $this->info('   ZOHO_CAMPAIGN_CLIENT_ID=your_new_client_id');
        $this->info('   ZOHO_CAMPAIGN_CLIENT_SECRET=your_new_client_secret');
        $this->info('   ZOHO_CAMPAIGN_REFRESH_TOKEN=your_new_refresh_token');
        $this->line('');
        
        $this->info('💡 Recommendation:');
        $this->info('Use Option 1 if your current app has Campaign permissions.');
        $this->info('Use Option 2 if the new app is specifically for Campaigns.');
        $this->line('');
        
        $this->info('🧪 After updating, test with: php artisan zoho:test-connection');
    }
} 