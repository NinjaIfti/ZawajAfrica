<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\AIEmailService;
use Carbon\Carbon;

class SendMatchSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:send-match-suggestions {--limit=50 : Maximum number of users to process} {--days=7 : Send to users active in the last X days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send AI-generated match suggestion emails to active users';

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
        $limit = (int) $this->option('limit');
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Sending match suggestions to users active since: {$cutoffDate->format('Y-m-d')}");

        // Get active users who haven't received match suggestions recently
        $users = User::where('last_login_at', '>=', $cutoffDate)
            ->whereHas('profile')
            ->where(function($query) {
                // Don't send to users who received suggestions in the last 3 days
                $query->whereDoesntExist(function($subQuery) {
                    $subQuery->selectRaw(1)
                        ->whereRaw("EXISTS (SELECT 1 FROM DUAL WHERE DATE(?) > DATE(?) - INTERVAL 3 DAY)", [
                            now()->toDateString(),
                            'last_match_email_sent'
                        ]);
                });
            })
            ->with(['profile', 'about'])
            ->limit($limit)
            ->get();

        if ($users->isEmpty()) {
            $this->info('No eligible users found for match suggestions.');
            return 0;
        }

        $this->info("Found {$users->count()} users eligible for match suggestions.");

        $successCount = 0;
        $failureCount = 0;

        foreach ($users as $user) {
            // Skip if user received match email recently
            if (cache()->has("match_email_sent_{$user->id}")) {
                continue;
            }

            $this->line("Sending match suggestions to: {$user->name} ({$user->email})");

            $result = $this->aiEmailService->sendMatchSuggestionsEmail($user);

            if ($result['success']) {
                $this->info("✓ Match suggestions sent successfully to {$user->name}");
                
                // Mark as sent (prevent sending again for 3 days)
                cache()->put("match_email_sent_{$user->id}", true, 60 * 60 * 24 * 3);
                
                $successCount++;
            } else {
                $this->error("✗ Failed to send match suggestions to {$user->name}: {$result['error']}");
                $failureCount++;
            }

            // Add delay to avoid overwhelming email service
            sleep(2);
        }

        $this->info("\nSummary:");
        $this->info("Successful: {$successCount}");
        $this->info("Failed: {$failureCount}");

        return 0;
    }
} 