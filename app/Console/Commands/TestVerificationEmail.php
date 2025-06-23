<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\VerificationApproved;
use App\Notifications\VerificationRejected;
use App\Services\ZohoMailService;
use Illuminate\Support\Facades\Log;

class TestVerificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:test-email {--user-id=1 : User ID to test with} {--type=approved : Type of notification (approved|rejected)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test verification email notifications using Zoho Mail';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Verification Email Notifications...');
        $this->newLine();

        // Check Zoho Mail service
        $zohoMailService = app(ZohoMailService::class);
        $status = $zohoMailService->getStatus();

        // Display configuration status
        $this->info('📋 Zoho Mail Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Configured', $status['configured'] ? '✅ Yes' : '❌ No'],
                ['SMTP Host', $status['smtp_host'] ?? 'Not set'],
                ['From Address', $status['from_address'] ?? 'Not set'],
                ['From Name', $status['from_name'] ?? 'Not set'],
            ]
        );
        $this->newLine();

        if (!$status['configured']) {
            $this->error('❌ Zoho Mail is not properly configured!');
            return 1;
        }

        // Get user
        $userId = $this->option('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found!");
            return 1;
        }

        $this->info("📧 Testing verification email for user: {$user->name} ({$user->email})");

        // Get notification type
        $type = $this->option('type');
        
        try {
            if ($type === 'approved') {
                $this->info('📨 Sending APPROVAL notification...');
                $user->notify(new VerificationApproved());
                
                $this->info('✅ Verification APPROVAL email sent successfully!');
                $this->newLine();
                
                $this->info('📄 Email Preview:');
                $this->line('Subject: 🎉 Verification Approved - Welcome to ZawajAfrica!');
                $this->line('To: ' . $user->email);
                $this->line('From: ' . $status['from_address']);
                $this->line('Content: Congratulations! Your account verification has been approved...');
                
            } elseif ($type === 'rejected') {
                $this->info('📨 Sending REJECTION notification...');
                $testReason = 'Test rejection: Document image quality needs improvement. Please ensure photos are clear and well-lit.';
                $user->notify(new VerificationRejected($testReason));
                
                $this->info('✅ Verification REJECTION email sent successfully!');
                $this->newLine();
                
                $this->info('📄 Email Preview:');
                $this->line('Subject: ❌ Verification Documents Need Review');
                $this->line('To: ' . $user->email);
                $this->line('From: ' . $status['from_address']);
                $this->line('Content: We need you to resubmit your verification documents...');
                $this->line('Reason: ' . $testReason);
                
            } else {
                $this->error('❌ Invalid notification type! Use "approved" or "rejected"');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send verification notification: ' . $e->getMessage());
            Log::error('TestVerificationEmail command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->newLine();
        $this->info('🎉 Verification email test completed successfully!');
        $this->info('📬 Check the email inbox for: ' . $user->email);
        
        return 0;
    }
} 