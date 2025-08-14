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

// Birthday reminder automation
Schedule::command('notifications:send-birthday-reminders --days=7')->dailyAt('10:00'); // Check for birthdays 7 days ahead
Schedule::command('notifications:send-birthday-reminders --days=1')->dailyAt('10:00'); // Check for birthdays 1 day ahead
Schedule::command('notifications:send-birthday-wishes')->dailyAt('09:00'); // Send direct birthday wishes to premium members

// Subscription management automation
Schedule::command('subscriptions:send-reminders')->dailyAt('08:00'); // Send subscription expiry reminders and notifications

// Upgrade reminder automation for free members
Schedule::command('notifications:send-upgrade-reminders')->dailyAt('14:00'); // Send upgrade reminders to free members
