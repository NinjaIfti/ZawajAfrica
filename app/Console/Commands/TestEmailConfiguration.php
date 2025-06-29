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
        $this->info('Testing Email Configuration...');

        // Get test email address
        $testEmail = $this->argument('email') ?? $this->ask('Enter email address to send test to:');

        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address provided');
            return 1;
        }

        // Test Zoho Mail Service
        $zohoMailService = app(ZohoMailService::class);
        
        $this->info('Checking Zoho Mail configuration...');
        $status = $zohoMailService->getStatus();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Configured', $status['configured'] ? '✅ Yes' : '❌ No'],
                ['SMTP Host', $status['smtp_host'] ?? 'Not set'],
                ['SMTP Port', $status['smtp_port'] ?? 'Not set'],
                ['Username', $status['smtp_username'] ?? 'Not set'],
                ['From Address', $status['from_address'] ?? 'Not set'],
                ['From Name', $status['from_name'] ?? 'Not set'],
            ]
        );

        if (!$status['configured']) {
            $this->error('Zoho Mail is not properly configured. Please check your environment variables.');
            return 1;
        }

        // Test email sending with timeout
        $this->info("Sending test email to: {$testEmail}");
        
        try {
            $startTime = microtime(true);
            
            // Configure Zoho Mail
            $zohoMailService->configureMailer();
            
            // Send test email
            Mail::raw('This is a test email from ZawajAfrica to verify email configuration and timeout settings.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                       ->subject('ZawajAfrica - Email Configuration Test - ' . now()->format('Y-m-d H:i:s'));
            });
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);
            
            $this->info("✅ Test email sent successfully!");
            $this->info("⏱️ Email sent in {$duration} seconds");
            
            if ($duration > 30) {
                $this->warn("⚠️ Email took longer than 30 seconds. Consider checking your SMTP settings.");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send test email: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'timeout') !== false) {
                $this->error("This appears to be a timeout issue. Check your SMTP timeout settings.");
            }
            
            return 1;
        }

        $this->info('Email configuration test completed successfully!');
        return 0;
    }
} 