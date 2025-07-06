<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserMatch;
use App\Notifications\BirthdayReminder;
use Carbon\Carbon;

class SendBirthdayReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-birthday-reminders {--days=7 : Days ahead to check for birthdays} {--limit=100 : Maximum number of reminders to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday reminders to users about their matched partners\' upcoming birthdays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = (int) $this->option('days');
        $limit = (int) $this->option('limit');
        
        $this->info("Checking for birthdays {$daysAhead} days ahead...");

        // Get all active matches
        $matches = UserMatch::with(['user1', 'user2'])
            ->where('status', 'active')
            ->whereNotNull('user1_id')
            ->whereNotNull('user2_id')
            ->limit($limit)
            ->get();

        if ($matches->isEmpty()) {
            $this->info('No active matches found.');
            return 0;
        }

        $this->info("Found {$matches->count()} active matches to check.");

        $remindersSent = 0;
        $errors = 0;

        foreach ($matches as $match) {
            try {
                // Check if user1's partner (user2) has an upcoming birthday
                $this->checkAndSendReminder($match->user1, $match->user2, $daysAhead);
                
                // Check if user2's partner (user1) has an upcoming birthday
                $this->checkAndSendReminder($match->user2, $match->user1, $daysAhead);
                
                $remindersSent++;
                
            } catch (\Exception $e) {
                $this->error("Error processing match {$match->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\nSummary:");
        $this->info("Matches processed: {$matches->count()}");
        $this->info("Reminders sent: {$remindersSent}");
        $this->info("Errors: {$errors}");

        return 0;
    }

    /**
     * Check if a partner has an upcoming birthday and send reminder
     */
    private function checkAndSendReminder(User $user, User $partner, int $daysAhead): void
    {
        // Skip if partner doesn't have complete birthday info
        if (!$partner->dob_day || !$partner->dob_month || !$partner->dob_year) {
            return;
        }

        // Convert month name to number
        $monthMap = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4,
            'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8,
            'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];

        $month = $monthMap[$partner->dob_month] ?? null;
        if (!$month) {
            return;
        }

        // Create partner's birthday for this year
        $birthdayThisYear = Carbon::createFromDate(date('Y'), $month, $partner->dob_day);
        
        // If birthday has passed this year, check next year
        if ($birthdayThisYear->isPast()) {
            $birthdayThisYear->addYear();
        }

        // Check if birthday is within the specified days ahead
        $daysUntilBirthday = now()->diffInDays($birthdayThisYear, false);
        
        if ($daysUntilBirthday <= $daysAhead && $daysUntilBirthday >= 0) {
            // Check if we already sent a reminder for this birthday
            $cacheKey = "birthday_reminder_{$user->id}_{$partner->id}_{$birthdayThisYear->format('Y-m-d')}";
            
            if (!cache()->has($cacheKey)) {
                $this->line("Sending birthday reminder to {$user->name} about {$partner->name}'s birthday in {$daysUntilBirthday} days");
                
                // Send the notification
                $user->notify(new BirthdayReminder($partner, $birthdayThisYear, $daysUntilBirthday));
                
                // Mark as sent to prevent duplicate reminders
                cache()->put($cacheKey, true, now()->addDays($daysAhead + 1));
                
                $this->info("✓ Birthday reminder sent to {$user->name}");
            } else {
                $this->line("Birthday reminder already sent to {$user->name} for {$partner->name}");
            }
        }
    }
} 