<?php

namespace App\Console\Commands;

use App\Services\MonnifyService;
use Illuminate\Console\Command;

class TestMonnify extends Command
{
    protected $signature = 'test:monnify';
    protected $description = 'Test Monnify configuration and credentials';

    public function handle(MonnifyService $monnifyService)
    {
        $this->info('Testing Monnify Configuration...');
        
        // Check if service is configured
        if (!$monnifyService->isConfigured()) {
            $this->error('❌ Monnify is not properly configured!');
            $this->line('Please ensure these environment variables are set:');
            $this->line('- MONNIFY_API_KEY');
            $this->line('- MONNIFY_SECRET_KEY');
            $this->line('- MONNIFY_CONTRACT_CODE');
            $this->line('- MONNIFY_BASE_URL');
            return 1;
        }
        
        $this->info('✅ Monnify configuration found');
        
        // Test authentication
        $this->info('Testing authentication...');
        try {
            $testPayment = [
                'amount' => 100,
                'customerName' => 'Test User',
                'customerEmail' => 'test@example.com',
                'paymentReference' => 'test_' . time(),
                'paymentDescription' => 'Test Payment',
                'redirectUrl' => 'https://example.com/callback',
                'metadata' => ['test' => true]
            ];
            
            $result = $monnifyService->initializePayment($testPayment);
            
            if ($result['status']) {
                $this->info('✅ Monnify authentication successful!');
                $this->info('Test payment initialization worked.');
                $this->line('Authorization URL: ' . $result['data']['authorization_url']);
            } else {
                $this->error('❌ Monnify payment initialization failed: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Monnify test failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 