<?php

namespace App\Console\Commands;

use App\Models\OtpVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP verification codes from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting OTP cleanup process...');

        // Count expired OTPs before deletion
        $expiredCount = OtpVerification::where('expires_at', '<', now())->count();

        if ($expiredCount === 0) {
            $this->info('No expired OTPs found.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiredCount} expired OTP record(s).");

        // Ask for confirmation unless --force flag is used
        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to delete {$expiredCount} expired OTP record(s)?")) {
                $this->info('Cleanup cancelled.');
                return self::SUCCESS;
            }
        }

        try {
            // Delete expired OTPs
            $deletedCount = OtpVerification::cleanupExpired();

            Log::info('OTP cleanup completed', [
                'deleted_count' => $deletedCount,
                'executed_by' => 'console_command'
            ]);

            $this->info("Successfully deleted {$deletedCount} expired OTP record(s).");

            // Also clean up old used OTPs (older than 7 days)
            $oldUsedCount = OtpVerification::where('is_used', true)
                ->where('used_at', '<', now()->subDays(7))
                ->count();

            if ($oldUsedCount > 0) {
                $this->info("Found {$oldUsedCount} old used OTP record(s) (older than 7 days).");
                
                if ($this->option('force') || $this->confirm("Do you want to delete these old used OTP records?")) {
                    $deletedOldCount = OtpVerification::where('is_used', true)
                        ->where('used_at', '<', now()->subDays(7))
                        ->delete();
                    
                    $this->info("Successfully deleted {$deletedOldCount} old used OTP record(s).");
                    
                    Log::info('Old OTP cleanup completed', [
                        'deleted_count' => $deletedOldCount,
                        'executed_by' => 'console_command'
                    ]);
                }
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during OTP cleanup: ' . $e->getMessage());
            
            Log::error('OTP cleanup failed', [
                'error' => $e->getMessage(),
                'executed_by' => 'console_command'
            ]);

            return self::FAILURE;
        }
    }
}
