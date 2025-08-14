<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\BirthdayWishes;
use Carbon\Carbon;

class SendBirthdayWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-birthday-wishes {--limit=50 : Maximum number of birthday wishes to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday wishes directly to premium members on their birthday';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Checking for premium members with birthdays today...");

        // Get premium members whose birthday is today
        $birthdayUsers = $this->getTodayBirthdayUsers($limit);

        if ($birthdayUsers->isEmpty()) {
            $this->info('No premium members with birthdays today.');
            return 0;
        }

        $this->info("Found {$birthdayUsers->count()} premium members with birthdays today.");

        $wishesSent = 0;
        $errors = 0;

        foreach ($birthdayUsers as $user) {
            try {
                // Check if we already sent birthday wishes today
                $cacheKey = "birthday_wishes_{$user->id}_" . now()->format('Y-m-d');
                
                if (!cache()->has($cacheKey)) {
                    $this->line("Sending birthday wishes to {$user->name}");
                    
                    // Send the notification
                    $user->notify(new BirthdayWishes($user));
                    
                    // Mark as sent to prevent duplicate wishes
                    cache()->put($cacheKey, true, now()->addDay());
                    
                    $this->info("✓ Birthday wishes sent to {$user->name}");
                    $wishesSent++;
                } else {
                    $this->line("Birthday wishes already sent to {$user->name}");
                }
                
            } catch (\Exception $e) {
                $this->error("Error sending birthday wishes to {$user->name}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\nSummary:");
        $this->info("Users processed: {$birthdayUsers->count()}");
        $this->info("Birthday wishes sent: {$wishesSent}");
        $this->info("Errors: {$errors}");

        return 0;
    }

    /**
     * Get premium users whose birthday is today
     */
    private function getTodayBirthdayUsers(int $limit)
    {
        $today = now();
        
        // Get month name mapping
        $monthMap = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $todayMonth = $monthMap[$today->month];
        $todayDay = $today->day;

        return User::where('subscription_status', 'active')
            ->whereNotNull('subscription_plan')
            ->where('dob_month', $todayMonth)
            ->where('dob_day', $todayDay)
            ->whereNotNull('dob_year')
            ->limit($limit)
            ->get();
    }
}
