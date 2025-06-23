<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\SubscriptionPurchased;
use App\Services\ZohoMailService;
use Illuminate\Support\Facades\Log;

class TestSubscriptionEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:test-email {--user-id=1 : User ID to test with} {--plan=Gold : Subscription plan to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test subscription purchase email notifications using Zoho Mail';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Subscription Purchase Email Notifications...');
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

        $this->info("📧 Testing subscription email for user: {$user->name} ({$user->email})");

        // Get plan
        $plan = $this->option('plan');
        $validPlans = ['Basic', 'Gold', 'Platinum'];
        
        if (!in_array($plan, $validPlans)) {
            $this->error("❌ Invalid plan! Valid plans: " . implode(', ', $validPlans));
            return 1;
        }

        // Plan pricing
        $planPricing = [
            'Basic' => 8000,
            'Gold' => 15000,
            'Platinum' => 25000
        ];

        $amount = $planPricing[$plan];
        $testPaymentReference = 'TEST_SUB_' . time();
        $expiresAt = now()->addMonth();
        
        try {
            $this->info("📨 Sending {$plan} subscription confirmation email...");
            
            $user->notify(new SubscriptionPurchased(
                $plan,
                $amount,
                $testPaymentReference,
                $expiresAt->toDateTime()
            ));
            
            $this->info('✅ Subscription confirmation email sent successfully!');
            $this->newLine();
            
            $this->info('📄 Email Preview:');
            $this->line('Subject: 🎉 Subscription Activated - Welcome to ' . $plan . ' Plan!');
            $this->line('To: ' . $user->email);
            $this->line('From: ' . $status['from_address']);
            $this->line('Plan: ' . $plan);
            $this->line('Amount: ₦' . number_format($amount, 2));
            $this->line('Reference: ' . $testPaymentReference);
            $this->line('Expires: ' . $expiresAt->format('l, F j, Y'));
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send subscription notification: ' . $e->getMessage());
            Log::error('TestSubscriptionEmail command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->newLine();
        $this->info('🎉 Subscription email test completed successfully!');
        $this->info('📬 Check the email inbox for: ' . $user->email);
        $this->info('💡 Tip: Also check spam/junk folder if email not received');
        
        return 0;
    }
} 