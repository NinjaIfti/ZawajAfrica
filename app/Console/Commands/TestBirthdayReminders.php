<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class TestBirthdayReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:birthday-reminders {--user-id= : Test with specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test birthday reminder logic and calculations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $this->testUserBirthday($user);
        } else {
            $this->testBirthdayCalculations();
        }

        return 0;
    }

    /**
     * Test birthday calculations for a specific user
     */
    private function testUserBirthday(User $user): void
    {
        $this->info("Testing birthday calculations for user: {$user->name} (ID: {$user->id})");
        
        if (!$user->dob_day || !$user->dob_month || !$user->dob_year) {
            $this->warn("User does not have complete birthday information.");
            return;
        }

        $this->info("Birthday: {$user->dob_day} {$user->dob_month} {$user->dob_year}");
        
        // Convert month name to number
        $monthMap = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4,
            'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8,
            'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];

        $month = $monthMap[$user->dob_month] ?? null;
        if (!$month) {
            $this->error("Invalid month: {$user->dob_month}");
            return;
        }

        // Create birthday for this year
        $birthdayThisYear = Carbon::createFromDate(date('Y'), $month, $user->dob_day);
        
        // If birthday has passed this year, check next year
        if ($birthdayThisYear->isPast()) {
            $birthdayThisYear->addYear();
        }

        $daysUntilBirthday = now()->diffInDays($birthdayThisYear, false);
        
        $this->info("Next birthday: {$birthdayThisYear->format('F j, Y')}");
        $this->info("Days until birthday: {$daysUntilBirthday}");
        
        if ($daysUntilBirthday <= 7 && $daysUntilBirthday >= 0) {
            $this->info("✅ This user's birthday is within 7 days!");
        } else {
            $this->info("❌ This user's birthday is not within 7 days.");
        }
    }

    /**
     * Test general birthday calculations
     */
    private function testBirthdayCalculations(): void
    {
        $this->info("Testing birthday calculation logic...");
        
        // Test with a sample birthday (tomorrow)
        $tomorrow = now()->addDay();
        $testBirthday = Carbon::createFromDate($tomorrow->year, $tomorrow->month, $tomorrow->day);
        
        $this->info("Test birthday: {$testBirthday->format('F j, Y')}");
        
        $daysUntil = now()->diffInDays($testBirthday, false);
        $this->info("Days until test birthday: {$daysUntil}");
        
        if ($daysUntil <= 7 && $daysUntil >= 0) {
            $this->info("✅ Test birthday is within 7 days!");
        } else {
            $this->info("❌ Test birthday is not within 7 days.");
        }
        
        // Test month conversion
        $this->info("\nTesting month conversion:");
        $monthMap = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4,
            'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8,
            'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];
        
        foreach ($monthMap as $monthName => $monthNumber) {
            $this->line("{$monthName} -> {$monthNumber}");
        }
    }
} 