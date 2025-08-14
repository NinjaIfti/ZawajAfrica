<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\UpgradeReminder;
use Carbon\Carbon;

class SendUpgradeReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-upgrade-reminders {--limit=50 : Maximum number of reminders to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send upgrade reminders to free members to encourage premium subscription';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting upgrade reminder process for free members...");

        $generalReminders = $this->sendGeneralReminders($limit);
        $activityReminders = $this->sendActivityBasedReminders($limit);

        $this->info("\nSummary:");
        $this->info("General reminders sent: {$generalReminders}");
        $this->info("Activity-based reminders sent: {$activityReminders}");

        return 0;
    }

    /**
     * Send general upgrade reminders to free members
     */
    private function sendGeneralReminders(int $limit): int
    {
        $this->info("Sending general upgrade reminders...");
        
        $remindersSent = 0;
        $now = Carbon::now();
        
        // Target free members who registered 3, 7, or 14 days ago
        $targetDays = [3, 7, 14];
        
        foreach ($targetDays as $days) {
            $targetDate = $now->copy()->subDays($days);
            
            // Get free members who registered on target date
            $freeUsers = User::where(function($query) {
                $query->whereNull('subscription_plan')
                      ->orWhere('subscription_status', '!=', 'active');
            })
            ->whereDate('created_at', $targetDate->format('Y-m-d'))
            ->where('email_verified_at', '!=', null) // Only verified users
            ->limit($limit)
            ->get();

            $this->line("Found {$freeUsers->count()} free members registered {$days} days ago");

            foreach ($freeUsers as $user) {
                try {
                    // Check if we already sent reminder for this period
                    $cacheKey = "upgrade_reminder_{$user->id}_{$days}days_{$targetDate->format('Y-m-d')}";
                    
                    if (!cache()->has($cacheKey)) {
                        $this->line("Sending {$days}-day upgrade reminder to {$user->name}");
                        
                        $reminderType = $this->getReminderType($days);
                        $user->notify(new UpgradeReminder($reminderType));
                        
                        // Mark as sent
                        cache()->put($cacheKey, true, now()->addDays(30));
                        
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
     * Send activity-based upgrade reminders
     */
    private function sendActivityBasedReminders(int $limit): int
    {
        $this->info("Sending activity-based upgrade reminders...");
        
        $remindersSent = 0;
        $now = Carbon::now();
        
        // Target active free members (logged in within last 7 days)
        $activeFreeUsers = User::where(function($query) {
            $query->whereNull('subscription_plan')
                  ->orWhere('subscription_status', '!=', 'active');
        })
        ->where('last_activity_at', '>=', $now->subDays(7))
        ->where('email_verified_at', '!=', null)
        ->limit($limit)
        ->get();

        $this->line("Found {$activeFreeUsers->count()} active free members");

        foreach ($activeFreeUsers as $user) {
            try {
                // Check if we sent an upgrade reminder in the last 7 days
                $cacheKey = "activity_upgrade_reminder_{$user->id}_" . $now->format('Y-m-d');
                $lastReminderKey = "last_upgrade_reminder_{$user->id}";
                
                $lastReminder = cache()->get($lastReminderKey);
                $daysSinceLastReminder = $lastReminder ? $now->diffInDays(Carbon::parse($lastReminder)) : 30;
                
                if (!cache()->has($cacheKey) && $daysSinceLastReminder >= 7) {
                    $this->line("Sending activity-based reminder to {$user->name}");
                    
                    $user->notify(new UpgradeReminder('activity'));
                    
                    // Mark as sent
                    cache()->put($cacheKey, true, now()->addDay());
                    cache()->put($lastReminderKey, $now->toDateString(), now()->addDays(30));
                    
                    $this->info("✓ Activity reminder sent to {$user->name}");
                    $remindersSent++;
                } else {
                    $this->line("Recent reminder already sent to {$user->name}");
                }
                
            } catch (\Exception $e) {
                $this->error("Error sending activity reminder to {$user->name}: " . $e->getMessage());
            }
        }
        
        return $remindersSent;
    }

    /**
     * Get reminder type based on days since registration
     */
    private function getReminderType(int $days): string
    {
        return match($days) {
            3 => 'general',
            7 => 'activity',
            14 => 'success_story',
            default => 'general'
        };
    }
}
