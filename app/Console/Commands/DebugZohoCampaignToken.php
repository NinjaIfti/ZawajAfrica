<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\ZohoCampaignService;

class DebugZohoCampaignToken extends Command
{
    protected $signature = 'zoho:debug-campaign-token';
    protected $description = 'Debug Zoho Campaign token refresh for the .com data center';

    public function handle()
    {
        $this->info('🔍 Debugging Zoho Campaign Token Refresh (.com data center)');
        $this->line('');
        
        // Test direct config access (like debug command)
        $this->testDirectConfig();
        $this->line('');
        
        // Test service access
        $this->testServiceConfig();
    }
    
    private function testDirectConfig()
    {
        $this->info('📋 Direct Config Access (Debug Command Method):');
        
        $clientId = config('services.zoho_campaign.client_id');
        $clientSecret = config('services.zoho_campaign.client_secret');
        $refreshToken = config('services.zoho_campaign.refresh_token');
        $tokenUrl = "https://accounts.zoho.com/oauth/v2/token";

        $this->info('  Client ID: ' . $clientId);
        $this->info('  Client Secret: ' . substr($clientSecret, 0, 8) . '...');
        $this->info('  Refresh Token: ' . substr($refreshToken, 0, 8) . '...');
        $this->line('');

        try {
            $response = Http::asForm()->timeout(30)->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['error'])) {
                    $this->error('  ❌ Direct config failed: ' . $json['error']);
                } else {
                    $this->info('  ✅ Direct config successful!');
                    $this->info('  Access Token: ' . substr($json['access_token'] ?? '', 0, 20) . '...');
                }
            } else {
                $this->error('  ❌ Direct config failed with status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('  ❌ Exception: ' . $e->getMessage());
        }
    }
    
    private function testServiceConfig()
    {
        $this->info('🔧 Service Config Access (ZohoCampaignService Method):');
        
        $service = new ZohoCampaignService();
        
        // Use reflection to access private properties
        $reflection = new \ReflectionClass($service);
        
        $clientIdProp = $reflection->getProperty('clientId');
        $clientIdProp->setAccessible(true);
        $clientId = $clientIdProp->getValue($service);
        
        $clientSecretProp = $reflection->getProperty('clientSecret');
        $clientSecretProp->setAccessible(true);
        $clientSecret = $clientSecretProp->getValue($service);
        
        $refreshTokenProp = $reflection->getProperty('refreshToken');
        $refreshTokenProp->setAccessible(true);
        $refreshToken = $refreshTokenProp->getValue($service);
        
        $this->info('  Client ID: ' . $clientId);
        $this->info('  Client Secret: ' . substr($clientSecret, 0, 8) . '...');
        $this->info('  Refresh Token: ' . substr($refreshToken, 0, 8) . '...');
        $this->line('');
        
        // Test token refresh through service
        $getAccessTokenMethod = $reflection->getMethod('getAccessToken');
        $getAccessTokenMethod->setAccessible(true);
        
        $token = $getAccessTokenMethod->invoke($service);
        
        if ($token) {
            $this->info('  ✅ Service config successful!');
            $this->info('  Access Token: ' . substr($token, 0, 20) . '...');
        } else {
            $this->error('  ❌ Service config failed!');
        }
        
        // Compare credentials
        $this->line('');
        $this->info('🔍 Credential Comparison:');
        $directClientId = config('services.zoho_campaign.client_id');
        $directClientSecret = config('services.zoho_campaign.client_secret');
        $directRefreshToken = config('services.zoho_campaign.refresh_token');
        
        $this->info('  Client ID Match: ' . ($clientId === $directClientId ? '✅ Yes' : '❌ No'));
        $this->info('  Client Secret Match: ' . ($clientSecret === $directClientSecret ? '✅ Yes' : '❌ No'));
        $this->info('  Refresh Token Match: ' . ($refreshToken === $directRefreshToken ? '✅ Yes' : '❌ No'));
        
        if ($clientSecret !== $directClientSecret) {
            $this->error('  Client Secret Mismatch Detected!');
            $this->error('  Service reads: ' . substr($clientSecret, 0, 15) . '...');
            $this->error('  Config reads: ' . substr($directClientSecret, 0, 15) . '...');
        }
    }
} 