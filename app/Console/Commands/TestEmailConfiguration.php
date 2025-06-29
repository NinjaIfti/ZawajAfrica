<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoMailService;
use Illuminate\Support\Facades\Mail;

class TestEmailConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test {email? : Email address to send test to}';

    /**
     * The console command description.
     */
    protected $description = 'Test email configuration and timeout settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Zoho HTTP API Email Configuration...');

        // Get test email address
        $testEmail = $this->argument('email') ?? $this->ask('Enter email address to send test to:');

        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address provided');
            return 1;
        }

        // Test Zoho Mail Service (now HTTP API only)
        $zohoMailService = app(ZohoMailService::class);
        
        $this->info('Checking Zoho HTTP API configuration...');
        $status = $zohoMailService->getStatus();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Configured', $status['configured'] ? '✅ Yes' : '❌ No'],
                ['Method', $status['method']],
                ['API URL', $status['api_url']],
                ['From Address', $status['from_address'] ?? 'Not set'],
                ['From Name', $status['from_name'] ?? 'Not set'],
                ['Has Token', $status['has_token'] ? '✅ Yes' : '❌ No'],
            ]
        );

        if (!$status['configured']) {
            $this->error('Zoho HTTP API is not properly configured. Please check your environment variables.');
            $this->warn('Required: ZOHO_MAIL_API_TOKEN, ZOHO_MAIL_FROM_ADDRESS');
            return 1;
        }

        // Test connection
        $this->info('Testing API connection...');
        $connectionResult = $zohoMailService->testConnection();
        
        if (!$connectionResult['success']) {
            $this->error('API connection failed: ' . $connectionResult['error']);
            return 1;
        }

        $this->info('✅ API connection successful');

        // Send test email
        $this->info("Sending test email to: {$testEmail}");
        
        try {
            $startTime = microtime(true);
            
            // Use ZohoHttpEmailService directly for better control
            $httpService = app(\App\Services\ZohoHttpEmailService::class);
            $result = $httpService->sendEmail(
                $testEmail,
                'ZawajAfrica - HTTP API Test - ' . now()->format('Y-m-d H:i:s'),
                '<h2>ZawajAfrica HTTP API Test</h2><p>This email was sent via Zoho HTTP API.</p><p>✅ No SMTP blocking issues!</p>'
            );
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);
            
            if ($result['success']) {
                $this->info("✅ Test email sent successfully via Zoho HTTP API!");
                $this->info("⏱️ Email sent in {$duration} seconds");
                
                if (isset($result['message_id'])) {
                    $this->info("📧 Message ID: {$result['message_id']}");
                }
            } else {
                $this->error("❌ Failed to send test email: " . $result['error']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: " . $e->getMessage());
            return 1;
        }

        $this->info('✅ Zoho HTTP API email configuration test completed successfully!');
        $this->info('🎉 No SMTP blocking issues - emails will work on DigitalOcean!');
        return 0;
    }
} 