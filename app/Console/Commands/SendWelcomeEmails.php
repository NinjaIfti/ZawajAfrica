<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\AIEmailService;
use Carbon\Carbon;

class SendWelcomeEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:send-welcome-emails {--hours=24 : Check for users registered in the last X hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send AI-generated welcome emails to new users';

    private AIEmailService $aiEmailService;

    public function __construct(AIEmailService $aiEmailService)
    {
        parent::__construct();
        $this->aiEmailService = $aiEmailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoffTime = Carbon::now()->subHours($hours);

        $this->info("Looking for users registered since: {$cutoffTime->format('Y-m-d H:i:s')}");

        $newUsers = User::where('created_at', '>=', $cutoffTime)
            ->whereDoesntHave('sentEmails', function($query) {
                $query->where('email_type', 'welcome');
            })
            ->with('profile')
            ->get();

        if ($newUsers->isEmpty()) {
            $this->info('No new users found who need welcome emails.');
            return 0;
        }

        $this->info("Found {$newUsers->count()} users who need welcome emails.");

        $successCount = 0;
        $failureCount = 0;

        foreach ($newUsers as $user) {
            $this->line("Sending welcome email to: {$user->name} ({$user->email})");

            $result = $this->aiEmailService->sendWelcomeEmail($user);

            if ($result['success']) {
                $this->info("✓ Welcome email sent successfully to {$user->name}");
                $this->recordEmailSent($user, 'welcome');
                $successCount++;
            } else {
                $this->error("✗ Failed to send welcome email to {$user->name}: {$result['error']}");
                $failureCount++;
            }

            // Add small delay to avoid overwhelming the email service
            sleep(1);
        }

        $this->info("\nSummary:");
        $this->info("Successful: {$successCount}");
        $this->info("Failed: {$failureCount}");

        return 0;
    }

    /**
     * Record that an email was sent to a user
     */
    private function recordEmailSent(User $user, string $emailType): void
    {
        // Create a simple tracking mechanism - you might want to create a proper model
        cache()->put("email_sent_{$user->id}_{$emailType}", true, 60 * 60 * 24 * 30); // 30 days
    }
} 