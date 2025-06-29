<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIEmailService;

class GenerateWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:weekly-reports {--force : Force send report even if already sent this week}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send AI-powered weekly reports to administrators';

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
        $this->info('Starting weekly report generation...');

        // Check if report was already sent this week
        $reportKey = 'weekly_report_sent_' . now()->format('Y_W');
        $force = $this->option('force');

        if (!$force && cache()->has($reportKey)) {
            $this->info('Weekly report already sent this week. Use --force to send again.');
            return 0;
        }

        try {
            $result = $this->aiEmailService->generateWeeklyReport();

            if ($result['success']) {
                $this->info('✓ Weekly report generated and sent successfully!');
                
                // Mark as sent for this week
                cache()->put($reportKey, true, 60 * 60 * 24 * 8); // 8 days
                
                return 0;
            } else {
                $this->error('✗ Failed to generate weekly report: ' . $result['error']);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('✗ Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }
} 