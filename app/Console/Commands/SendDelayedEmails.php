<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MatchController;

class SendDelayedEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'emails:send-delayed {--force : Force send all scheduled emails}';

    /**
     * The console command description.
     */
    protected $description = 'Send delayed like and match notification emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕐 Processing delayed email notifications...');
        
        try {
            $matchController = app(MatchController::class);
            $matchController->sendScheduledEmails();
            
            $this->info('✅ Delayed emails processed successfully');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to process delayed emails: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 