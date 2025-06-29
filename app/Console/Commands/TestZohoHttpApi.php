<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoHttpEmailService;

class TestZohoHttpApi extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:test-http {email? : Email address to send test to}';

    /**
     * The console command description.
     */
    protected $description = 'Test Zoho HTTP API email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Zoho HTTP API Email Service');
        $this->info('=====================================');

        // Get test email address
        $testEmail = $this->argument('email') ?? $this->ask('Enter email address to send test to:');

        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address provided');
            return 1;
        }

        // Initialize service
        $zohoHttpService = app(ZohoHttpEmailService::class);
        
        // Check configuration
        $this->info('Checking Zoho HTTP API configuration...');
        $status = $zohoHttpService->getStatus();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Configured', $status['configured'] ? '✅ Yes' : '❌ No'],
                ['API URL', $status['api_url']],
                ['Account ID', $status['account_id'] ?: '❌ Not set'],
                ['From Email', $status['from_email']],
                ['From Name', $status['from_name']],
                ['Has Token', $status['has_token'] ? '✅ Yes' : '❌ No'],
                ['Has Account ID', $status['has_account_id'] ? '✅ Yes' : '❌ No'],
            ]
        );

        if (!$status['configured']) {
            $this->error('❌ Zoho HTTP API is not properly configured.');
            
            if (!$status['has_token']) {
                $this->warn('Please set ZOHO_MAIL_API_TOKEN in your .env file');
            }
            
            if (!$status['has_account_id']) {
                $this->warn('Please set ZOHO_MAIL_ACCOUNT_ID in your .env file');
            }
            
            $this->info('To configure Zoho Mail API:');
            $this->info('1. Go to https://accounts.zoho.com/developerconsole');
            $this->info('2. Create a Server-based Application');
            $this->info('3. Generate access token with ZohoMail.messages.ALL scope');
            $this->info('4. Get your Account ID from Zoho Mail API (Get All User Accounts API)');
            $this->info('5. Add both ZOHO_MAIL_API_TOKEN and ZOHO_MAIL_ACCOUNT_ID to .env');
            return 1;
        }

        // Test connection
        $this->info('Testing API connection...');
        $connectionTest = $zohoHttpService->testConnection();
        
        if ($connectionTest['success']) {
            $this->info('✅ API connection successful');
        } else {
            $this->error('❌ API connection failed: ' . $connectionTest['error']);
            return 1;
        }

        // Send test email
        $this->info("Sending test email to: {$testEmail}");
        
        try {
            $startTime = microtime(true);
            
            $subject = 'ZawajAfrica - Zoho HTTP API Test - ' . now()->format('Y-m-d H:i:s');
            $body = '<h2>Zoho HTTP API Test Email</h2>
                     <p>This email was sent via Zoho HTTP API to test the configuration.</p>
                     <p><strong>Sent at:</strong> ' . now()->format('Y-m-d H:i:s') . '</p>
                     <p><strong>Service:</strong> Zoho HTTP Email API</p>
                     <p><strong>Platform:</strong> ZawajAfrica</p>
                     <hr>
                     <small>If you received this email, the Zoho HTTP API is working correctly!</small>';
            
            $result = $zohoHttpService->sendEmail($testEmail, $subject, $body);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);
            
            if ($result['success']) {
                $this->info("✅ Test email sent successfully via Zoho HTTP API!");
                $this->info("⏱️ Email sent in {$duration} seconds");
                
                if (isset($result['message_id'])) {
                    $this->info("📧 Message ID: {$result['message_id']}");
                }
                
                $this->info('🎉 Zoho HTTP API is working correctly!');
                
                // Test different sender types
                $this->info('Testing different sender types...');
                
                $senderTypes = ['support', 'admin', 'noreply'];
                foreach ($senderTypes as $senderType) {
                    $this->info("Testing {$senderType} sender...");
                    
                    $testResult = $zohoHttpService->sendNotificationEmail(
                        $senderType,
                        $testEmail,
                        "ZawajAfrica - {$senderType} Test",
                        "<p>This is a test email from the {$senderType} sender type.</p>"
                    );
                    
                    if ($testResult['success']) {
                        $this->info("  ✅ {$senderType} sender working");
                    } else {
                        $this->warn("  ⚠️ {$senderType} sender failed: " . $testResult['error']);
                    }
                }
                
            } else {
                $this->error("❌ Failed to send test email: " . $result['error']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: " . $e->getMessage());
            return 1;
        }

        $this->info('✅ Zoho HTTP API test completed successfully!');
        return 0;
    }
} 