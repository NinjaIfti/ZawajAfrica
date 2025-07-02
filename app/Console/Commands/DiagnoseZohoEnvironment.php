<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Services\ZohoCampaignService;

class DiagnoseZohoEnvironment extends Command
{
    protected $signature = 'zoho:diagnose-environment';
    protected $description = 'Comprehensive diagnosis of Zoho Campaign environment differences';

    public function handle()
    {
        $this->info('🔍 Comprehensive Zoho Campaign Environment Diagnosis');
        $this->line('');

        // 1. Check configuration access
        $this->checkConfiguration();
        
        // 2. Test direct token refresh (like debug command)
        $this->testDirectTokenRefresh();
        
        // 3. Test service token refresh
        $this->testServiceTokenRefresh();
        
        // 4. Check cache
        $this->checkCache();
        
        // 5. Test API call through service
        $this->testServiceApiCall();
    }

    private function checkConfiguration()
    {
        $this->info('📋 Configuration Check:');
        
        $configs = [
            'client_id' => config('services.zoho_campaign.client_id'),
            'client_secret' => config('services.zoho_campaign.client_secret'),
            'refresh_token' => config('services.zoho_campaign.refresh_token'),
            'from_email' => config('services.zoho_campaign.from_email'),
            'from_name' => config('services.zoho_campaign.from_name'),
            'data_center' => config('services.zoho_campaign.data_center'),
        ];

        foreach ($configs as $key => $value) {
            if ($key === 'client_secret' || $key === 'refresh_token') {
                $display = $value ? substr($value, 0, 8) . '...' : 'NULL';
            } else {
                $display = $value ?: 'NULL';
            }
            $this->line("  {$key}: {$display}");
        }
        $this->line('');
    }

    private function testDirectTokenRefresh()
    {
        $this->info('🔧 Direct Token Refresh (CLI Method):');
        
        $clientId = config('services.zoho_campaign.client_id');
        $clientSecret = config('services.zoho_campaign.client_secret');
        $refreshToken = config('services.zoho_campaign.refresh_token');
        $tokenUrl = "https://accounts.zoho.com/oauth/v2/token";

        try {
            $response = Http::asForm()->timeout(30)->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('  ✅ SUCCESS: Direct token refresh worked');
                $this->info('  Access Token: ' . substr($data['access_token'] ?? '', 0, 20) . '...');
                $this->info('  Expires In: ' . ($data['expires_in'] ?? 'Unknown') . ' seconds');
            } else {
                $this->error('  ❌ FAILED: Direct token refresh failed');
                $this->error('  Status: ' . $response->status());
                $this->error('  Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('  ❌ EXCEPTION: ' . $e->getMessage());
        }
        $this->line('');
    }

    private function testServiceTokenRefresh()
    {
        $this->info('🔧 Service Token Refresh:');
        
        try {
            $service = new ZohoCampaignService();
            
            // Clear any cached token first
            Cache::forget('zoho_campaign_access_token');
            
            // Use reflection to access private method
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('getAccessToken');
            $method->setAccessible(true);
            
            $token = $method->invoke($service);
            
            if ($token) {
                $this->info('  ✅ SUCCESS: Service token refresh worked');
                $this->info('  Access Token: ' . substr($token, 0, 20) . '...');
            } else {
                $this->error('  ❌ FAILED: Service token refresh returned null');
            }
        } catch (\Exception $e) {
            $this->error('  ❌ EXCEPTION: ' . $e->getMessage());
        }
        $this->line('');
    }

    private function checkCache()
    {
        $this->info('🗄️ Cache Check:');
        
        $cacheKey = 'zoho_campaign_access_token';
        $cachedToken = Cache::get($cacheKey);
        
        if ($cachedToken) {
            $this->info('  Cached Token: ' . substr($cachedToken, 0, 20) . '...');
        } else {
            $this->info('  No cached token found');
        }
        
        // Clear cache for clean test
        Cache::forget($cacheKey);
        $this->info('  Cache cleared for clean testing');
        $this->line('');
    }

    private function testServiceApiCall()
    {
        $this->info('🌐 Service API Call Test:');
        
        try {
            $service = new ZohoCampaignService();
            $result = $service->getMailingLists();
            
            if ($result['success']) {
                $this->info('  ✅ SUCCESS: API call through service worked');
                $this->info('  Lists found: ' . count($result['lists'] ?? []));
            } else {
                $this->error('  ❌ FAILED: API call through service failed');
                $this->error('  Error: ' . ($result['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error('  ❌ EXCEPTION: ' . $e->getMessage());
        }
        $this->line('');
    }
} 