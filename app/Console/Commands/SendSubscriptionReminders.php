<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\SubscriptionExpiring;
use App\Notifications\SubscriptionExpired;
use Carbon\Carbon;

class SendSubscriptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-reminders {--limit=100 : Maximum number of reminders to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send subscription expiry reminders and notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting subscription reminder process...");

        $expiringReminders = $this->sendExpiringReminders($limit);
        $expiredNotifications = $this->sendExpiredNotifications($limit);

        $this->info("\nSummary:");
        $this->info("Expiring reminders sent: {$expiringReminders}");
        $this->info("Expired notifications sent: {$expiredNotifications}");

        return 0;
    }

    /**
     * Send reminders for subscriptions expiring soon
     */
    private function sendExpiringReminders(int $limit): int
    {
        $this->info("Checking for subscriptions expiring soon...");
        
        $remindersSent = 0;
        $now = Carbon::now();
        
        // Send reminders for subscriptions expiring in 7, 3, and 1 days
        $reminderDays = [7, 3, 1];
        
        foreach ($reminderDays as $days) {
            $targetDate = $now->copy()->addDays($days);
            
            // Get users whose subscriptions expire on the target date
            $expiringUsers = User::where('subscription_status', 'active')
                ->whereNotNull('subscription_plan')
                ->whereNotNull('subscription_expires_at')
                ->whereDate('subscription_expires_at', $targetDate->format('Y-m-d'))
                ->limit($limit)
                ->get();

            $this->line("Found {$expiringUsers->count()} subscriptions expiring in {$days} days");

            foreach ($expiringUsers as $user) {
                try {
                    // Check if we already sent reminder for this period
                    $cacheKey = "subscription_reminder_{$user->id}_{$days}days_{$targetDate->format('Y-m-d')}";
                    
                    if (!cache()->has($cacheKey)) {
                        $this->line("Sending {$days}-day reminder to {$user->name} ({$user->subscription_plan})");
                        
                        $user->notify(new SubscriptionExpiring(
                            $user->subscription_plan,
                            $user->subscription_expires_at,
                            $days
                        ));
                        
                        // Mark as sent
                        cache()->put($cacheKey, true, now()->addDays($days + 1));
                        
                        $this->info("✓ Reminder sent to {$user->name}");
                        $remindersSent++;
                    } else {
                        $this->line("Reminder already sent to {$user->name} for {$days} days");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("Error sending reminder to {$user->name}: " . $e->getMessage());
                }
            }
        }
        
        return $remindersSent;
    }

    /**
     * Send notifications for expired subscriptions
     */
    private function sendExpiredNotifications(int $limit): int
    {
        $this->info("Checking for newly expired subscriptions...");
        
        $notificationsSent = 0;
        $yesterday = Carbon::now()->subDay();
        
        // Get users whose subscriptions expired yesterday (newly expired)
        $expiredUsers = User::where('subscription_status', 'expired')
            ->whereNotNull('subscription_plan')
            ->whereNotNull('subscription_expires_at')
            ->whereDate('subscription_expires_at', $yesterday->format('Y-m-d'))
            ->limit($limit)
            ->get();

        $this->line("Found {$expiredUsers->count()} newly expired subscriptions");

        foreach ($expiredUsers as $user) {
            try {
                // Check if we already sent expiry notification
                $cacheKey = "subscription_expired_{$user->id}_{$yesterday->format('Y-m-d')}";
                
                if (!cache()->has($cacheKey)) {
                    $this->line("Sending expiry notification to {$user->name} ({$user->subscription_plan})");
                    
                    $user->notify(new SubscriptionExpired(
                        $user->subscription_plan,
                        $user->subscription_expires_at
                    ));
                    
                    // Mark as sent
                    cache()->put($cacheKey, true, now()->addDays(7));
                    
                    $this->info("✓ Expiry notification sent to {$user->name}");
                    $notificationsSent++;
                } else {
                    $this->line("Expiry notification already sent to {$user->name}");
                }
                
            } catch (\Exception $e) {
                $this->error("Error sending expiry notification to {$user->name}: " . $e->getMessage());
            }
        }
        
        return $notificationsSent;
    }
}
