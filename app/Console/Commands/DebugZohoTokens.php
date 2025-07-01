<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DebugZohoTokens extends Command
{
    protected $signature = 'zoho:debug-tokens';
    protected $description = 'Debug Zoho Campaign token refresh issues';

    public function handle()
    {
        $this->info('🔍 Debugging Zoho Campaign Tokens...');
        $this->line('');
        
        // Get config values
        $clientId = config('services.zoho_campaign.client_id');
        $clientSecret = config('services.zoho_campaign.client_secret');
        $refreshToken = config('services.zoho_campaign.refresh_token');
        $fromEmail = config('services.zoho_campaign.from_email');
        
        // Check configuration
        $this->info('📋 Configuration Check:');
        $this->info("Client ID: " . ($clientId ? '✅ Present' : '❌ Missing'));
        $this->info("Client Secret: " . ($clientSecret ? '✅ Present' : '❌ Missing'));
        $this->info("Refresh Token: " . ($refreshToken ? '✅ Present' : '❌ Missing'));
        $this->info("From Email: " . ($fromEmail ? '✅ Present' : '❌ Missing'));
        $this->line('');
        
        if (!$clientId || !$clientSecret || !$refreshToken) {
            $this->error('❌ Missing required configuration. Please check your .env file.');
            return;
        }
        
        // Show token info
        $this->info('🔑 Token Information:');
        $this->info("Refresh Token (first 20 chars): " . substr($refreshToken, 0, 20) . "...");
        $this->info("Refresh Token Length: " . strlen($refreshToken) . " characters");
        $this->line('');
        
        // Test token refresh with detailed logging
        $this->info('🧪 Testing Token Refresh...');
        
        try {
            $requestData = [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken
            ];
            
            $this->info('Request URL: https://accounts.zoho.com/oauth/v2/token');
            $this->info('Request Method: POST');
            $this->info('Request Data:');
            foreach ($requestData as $key => $value) {
                if ($key === 'client_secret' || $key === 'refresh_token') {
                    $this->info("  {$key}: " . substr($value, 0, 10) . "...");
                } else {
                    $this->info("  {$key}: {$value}");
                }
            }
            $this->line('');
            
            $response = Http::timeout(30)->post('https://accounts.zoho.com/oauth/v2/token', $requestData);
            
            $this->info('📡 Response Details:');
            $this->info("Status Code: " . $response->status());
            $this->info("Content Type: " . $response->header('Content-Type'));
            $this->line('');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Token refresh successful!');
                $this->info("Access Token (first 20 chars): " . substr($data['access_token'] ?? '', 0, 20) . "...");
                $this->info("Expires In: " . ($data['expires_in'] ?? 'Unknown') . " seconds");
                
                // Test API call with new token
                $this->info('🎯 Testing API Call with New Token...');
                $apiResponse = Http::withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $data['access_token'],
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])->get('https://campaigns.zoho.com/api/v1.1/xml/listcollection', [
                    'resfmt' => 'json'
                ]);
                
                if ($apiResponse->successful()) {
                    $this->info('✅ API call successful! Your tokens are working.');
                } else {
                    $this->error('❌ API call failed: ' . $apiResponse->status());
                    $this->error('Response: ' . $apiResponse->body());
                }
                
            } else {
                $this->error('❌ Token refresh failed!');
                $this->error("Status Code: " . $response->status());
                
                $body = $response->body();
                $this->line('');
                $this->info('📄 Response Body:');
                
                // Check if it's HTML (error page) or JSON
                if (str_contains($body, '<html>')) {
                    $this->error('Received HTML error page instead of JSON response.');
                    $this->error('This typically means:');
                    $this->error('1. The refresh token is invalid/expired');
                    $this->error('2. The client credentials are incorrect');
                    $this->error('3. The token was revoked');
                    $this->line('');
                    $this->info('💡 Solutions:');
                    $this->info('1. Generate a new refresh token using: php artisan zoho:generate-tokens');
                    $this->info('2. Check your client credentials in .env file');
                    $this->info('3. Ensure your Zoho app has Campaign API permissions');
                } else {
                    // Try to parse as JSON
                    try {
                        $errorData = $response->json();
                        $this->error('JSON Error Response:');
                        foreach ($errorData as $key => $value) {
                            $this->error("  {$key}: {$value}");
                        }
                    } catch (\Exception $e) {
                        $this->error('Raw Response:');
                        $this->error(substr($body, 0, 500) . (strlen($body) > 500 ? '...' : ''));
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred:');
            $this->error($e->getMessage());
            $this->line('');
            $this->info('This might be a network issue or invalid request format.');
        }
        
        $this->line('');
        $this->info('🔧 Next Steps:');
        $this->info('1. If token refresh failed, generate new tokens: php artisan zoho:generate-tokens');
        $this->info('2. Double-check your .env file has the correct values');
        $this->info('3. Ensure your Zoho app is active and has proper permissions');
    }
} 