<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoCampaignService;

class TestZohoCampaignApi extends Command
{
    protected $signature = 'zoho:test-campaign';
    protected $description = 'Test Zoho Campaign API connection and configuration';

    public function handle()
    {
        $this->info('🔍 Testing Zoho Campaign API...');
        $this->line('');

        $zohoCampaignService = new ZohoCampaignService();
        $status = $zohoCampaignService->getStatus();

        // Configuration Check
        $this->info('📋 Configuration Status:');
        $this->info('Enabled: ' . (config('services.zoho_campaign.enabled') ? '✅ Yes' : '❌ No'));
        $this->info('Client ID: ' . ($status['has_client_secret'] ? '✅ Present' : '❌ Missing'));
        $this->info('Client Secret: ' . ($status['has_client_secret'] ? '✅ Present' : '❌ Missing'));
        $this->info('Refresh Token: ' . ($status['has_refresh_token'] ? '✅ Present' : '❌ Missing'));
        $this->info('From Email: ' . ($status['from_email'] ? '✅ ' . $status['from_email'] : '❌ Missing'));
        $this->info('From Name: ' . ($status['from_name'] ? '✅ ' . $status['from_name'] : '❌ Missing'));
        $this->info('Data Center: ' . $status['data_center']);
        $this->info('API URL: ' . $status['base_url']);
        $this->line('');

        if (!$status['configured']) {
            $this->error('❌ Zoho Campaign is not properly configured.');
            $this->line('');
            $this->info('Please ensure these environment variables are set:');
            $this->info('ZOHO_CAMPAIGN_ENABLED=true');
            $this->info('ZOHO_CAMPAIGN_CLIENT_ID=your_client_id');
            $this->info('ZOHO_CAMPAIGN_CLIENT_SECRET=your_client_secret');
            $this->info('ZOHO_CAMPAIGN_REFRESH_TOKEN=your_refresh_token');
            $this->info('ZOHO_CAMPAIGN_FROM_EMAIL=admin@zawajafrica.com.ng');
            $this->info('ZOHO_CAMPAIGN_FROM_NAME=ZawajAfrica');
            $this->line('');
            $this->info('Run: php artisan zoho:generate-tokens for token generation help');
            return 1;
        }

        // Test Connection
        $this->info('🧪 Testing API Connection...');
        $connectionTest = $zohoCampaignService->testConnection();
        
        if ($connectionTest['success']) {
            $this->info('✅ ' . $connectionTest['message']);
            
            // Test fetching all mailing lists
            $this->info('📋 Fetching All Mailing Lists...');
            $listsResult = $zohoCampaignService->getMailingLists();
            
            if ($listsResult['success']) {
                $lists = $listsResult['lists'] ?? [];
                $this->info("✅ Found " . count($lists) . " mailing lists:");
                
                foreach ($lists as $list) {
                    $this->line("  • {$list['listname']} (Key: " . substr($list['listkey'], 0, 12) . "...) - {$list['noofcontacts']} contacts");
                }
            } else {
                $this->error('❌ Failed to fetch lists: ' . $listsResult['error']);
            }
            
            $this->line('');
            
            // Test creating a new list
            $this->info('📝 Testing New List Creation...');
            $testListName = 'Test List ' . now()->format('Y-m-d H:i:s');
            $this->info("Creating list: {$testListName}");
            
            $createResult = $zohoCampaignService->createMailingList($testListName, 'Test list for debugging');
            
            if ($createResult['success']) {
                $this->info('✅ List created successfully');
                $listKey = $createResult['data']['listkey'] ?? 'unknown';
                $this->info("📋 List key: {$listKey}");
            } else {
                $this->error('❌ List creation failed: ' . $createResult['error']);
            }
            
            $this->line('');
            
            // Test adding real users to existing list
            $this->info('👥 Testing Real User Import to Existing List...');
            $existingListKey = '3z3530fad97c99f2dbaad8a77652b299fd73cd92b6903939941354f60a34fa4974';
            $this->info("Using existing list key: {$existingListKey}");
            
            // Import a small sample of users
            $importResult = $zohoCampaignService->importUsers('all', '', $existingListKey);
            
            if ($importResult['success']) {
                $this->info('✅ ' . $importResult['message']);
                $this->info("📊 Users imported: {$importResult['users_imported']}");
                if (isset($importResult['failed_batches']) && $importResult['failed_batches'] > 0) {
                    $this->warn("⚠️  Failed batches: {$importResult['failed_batches']}");
                }
            } else {
                $this->error('❌ Import failed: ' . $importResult['error']);
            }
        } else {
            $this->error('❌ ' . $connectionTest['error']);
            return 1;
        }

        $this->line('');
        $this->info('🎉 Zoho Campaign API test completed successfully!');
        $this->line('');
        $this->info('Next steps:');
        $this->info('1. Visit your admin panel → AI Insights → Zoho Campaigns tab');
        $this->info('2. Import users to create mailing lists');
        $this->info('3. Create and send campaigns to your users');
        
        return 0;
    }
} 