<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription cleanup to run every hour
Schedule::command('subscriptions:cleanup')->hourly();


// Birthday reminder automation
Schedule::command('notifications:send-birthday-reminders --days=7')->dailyAt('10:00'); // Check for birthdays 7 days ahead
Schedule::command('notifications:send-birthday-reminders --days=1')->dailyAt('10:00'); // Check for birthdays 1 day ahead
Schedule::command('notifications:send-birthday-wishes')->dailyAt('09:00'); // Send direct birthday wishes to premium members

// Subscription management automation
Schedule::command('subscriptions:send-reminders')->dailyAt('08:00'); // Send subscription expiry reminders and notifications

// Upgrade reminder automation for free members
Schedule::command('notifications:send-upgrade-reminders')->dailyAt('14:00'); // Send upgrade reminders to free members
