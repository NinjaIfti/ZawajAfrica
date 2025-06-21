<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoMailService;
use App\Models\User;
use App\Models\Therapist;
use App\Models\TherapistBooking;
use App\Notifications\TherapistBookingConfirmed;
use Illuminate\Support\Facades\Log;

class TestZohoEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:test-email {--user-id=1 : User ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Zoho Mail integration with ZawajAfrica notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Zoho Mail Integration...');
        $this->newLine();

        // Check Zoho Mail service
        $zohoMailService = app(ZohoMailService::class);
        $status = $zohoMailService->getStatus();

        // Display configuration status
        $this->info('📋 Configuration Status:');
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
        $this->newLine();

        if (!$status['configured']) {
            $this->error('❌ Zoho Mail is not properly configured!');
            $this->info('💡 Add these to your .env file:');
            $this->line('ZOHO_MAIL_ENABLED=true');
            $this->line('ZOHO_MAIL_USERNAME=support@zawajafrica.online');
            $this->line('ZOHO_MAIL_PASSWORD=your_app_password');
            $this->line('ZOHO_MAIL_FROM_ADDRESS=support@zawajafrica.online');
            $this->line('ZOHO_MAIL_FROM_NAME="ZawajAfrica Support"');
            return 1;
        }

        // Test basic connectivity
        $this->info('🔗 Testing SMTP connectivity...');
        $testResult = $zohoMailService->testConnection();
        
        if ($testResult['status']) {
            $this->info('✅ SMTP test: ' . $testResult['message']);
        } else {
            $this->error('❌ SMTP test failed: ' . $testResult['message']);
            return 1;
        }
        $this->newLine();

        // Test with notification system
        $userId = $this->option('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found!");
            return 1;
        }

        $this->info("📧 Testing notification email for user: {$user->name} ({$user->email})");

        // Create a test booking
        $therapist = Therapist::first();
        if (!$therapist) {
            $this->error('❌ No therapist found in database for testing!');
            return 1;
        }

        // Create a mock booking for testing
        $testBooking = new TherapistBooking([
            'id' => 999999,
            'user_id' => $user->id,
            'therapist_id' => $therapist->id,
            'appointment_datetime' => now()->addDays(3)->setTime(14, 0),
            'session_type' => 'video_call',
            'amount' => $therapist->hourly_rate ?? 5000,
            'payment_reference' => 'TEST_' . time(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'meeting_link' => 'https://meet.google.com/test-session-123'
        ]);

        // Set relationships for the test
        $testBooking->setRelation('therapist', $therapist);
        $testBooking->setRelation('user', $user);

        try {
            // Send test notification
            $user->notify(new TherapistBookingConfirmed($testBooking));
            
            $this->info('✅ Test notification sent successfully!');
            $this->info('📬 Check the email inbox for: ' . $user->email);
            $this->newLine();
            
            $this->info('📄 Email Preview:');
            $this->line('Subject: ✅ Therapy Session Confirmed!');
            $this->line('To: ' . $user->email);
            $this->line('From: ' . $status['from_address']);
            $this->line('Therapist: ' . $therapist->name);
            $this->line('Date: ' . $testBooking->appointment_datetime->format('l, F j, Y \a\t g:i A'));
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test notification: ' . $e->getMessage());
            Log::error('TestZohoEmail command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->newLine();
        $this->info('🎉 Zoho Mail integration test completed successfully!');
        
        return 0;
    }
} 