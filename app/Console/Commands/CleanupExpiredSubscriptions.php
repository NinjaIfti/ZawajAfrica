<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class CleanupExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:cleanup 
                            {--batch=100 : Number of users to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired subscriptions as expired in batch to improve performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch');
        $now = Carbon::now();
        
        $this->info('Starting subscription cleanup...');
        
        // Find users with active subscriptions that have expired
        $expiredUsers = User::where('subscription_status', 'active')
            ->where('subscription_expires_at', '<', $now)
            ->whereNotNull('subscription_expires_at');
            
        $totalCount = $expiredUsers->count();
        
        if ($totalCount === 0) {
            $this->info('No expired subscriptions found.');
            return;
        }
        
        $this->info("Found {$totalCount} expired subscriptions to update.");
        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();
        
        $processedCount = 0;
        
        // Process in batches
        $expiredUsers->chunk($batchSize, function ($users) use (&$processedCount, $progressBar) {
            $userIds = $users->pluck('id')->toArray();
            
            // Batch update all expired subscriptions
            User::whereIn('id', $userIds)->update([
                'subscription_status' => 'expired',
                'updated_at' => now()
            ]);
            
            $processedCount += count($userIds);
            $progressBar->advance(count($userIds));
        });
        
        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully processed {$processedCount} expired subscriptions.");
        
        // Log the cleanup activity
        \Log::info("Subscription cleanup completed: {$processedCount} subscriptions marked as expired");
    }
} 