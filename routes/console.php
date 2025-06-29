<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription cleanup to run every hour
Schedule::command('subscriptions:cleanup')->hourly();

// AI-powered automation schedules
Schedule::command('ai:send-welcome-emails')->dailyAt('09:00'); // Send welcome emails daily at 9 AM
Schedule::command('ai:weekly-reports')->weeklyOn(1, '10:00'); // Send weekly reports on Monday at 10 AM
Schedule::command('ai:send-match-suggestions')->twiceDaily(10, 18); // Send match suggestions at 10 AM and 6 PM
Schedule::command('emails:send-delayed')->everyMinute()->withoutOverlapping(); // Process delayed like/match emails
Schedule::command('therapist:send-reminders')->hourly(); // Keep existing therapist reminders
