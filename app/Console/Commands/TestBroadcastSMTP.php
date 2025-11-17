<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailerSendService;
use App\Models\User;

class TestBroadcastSMTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-broadcast-smtp {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MailerSend SMTP broadcast system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing MailerSend SMTP Broadcast System...');
        $this->newLine();
        
        // Display SMTP configuration
        $this->info('SMTP Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
                ['Encryption', config('mail.mailers.smtp.encryption')],
                ['Username', config('mail.mailers.smtp.username')],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );
        
        $this->newLine();
        
        // Get test email
        $testEmail = $this->argument('email') ?? config('mail.from.address');
        
        $this->info("Sending test broadcast to: {$testEmail}");
        $this->newLine();
        
        try {
            $mailerSendService = app(MailerSendService::class);
            
            // Check if configured
            if (!$mailerSendService->isConfigured()) {
                $this->error('❌ MailerSend SMTP not configured!');
                $this->warn('Please check your .env file for SMTP settings.');
                return Command::FAILURE;
            }
            
            $this->info('✅ SMTP configuration detected');
            $this->newLine();
            
            // Prepare test recipients
            $recipients = [
                [
                    'email' => $testEmail,
                    'name' => 'Test User'
                ]
            ];
            
            $subject = 'Test Broadcast - MailerSend SMTP - ' . now()->format('Y-m-d H:i:s');
            $body = "This is a test broadcast email from ZawajAfrica's admin broadcast system.

The broadcast system is now using MailerSend SMTP instead of the API.

Test Details:
- Server: " . config('mail.mailers.smtp.host') . "
- Port: " . config('mail.mailers.smtp.port') . "
- Encryption: " . config('mail.mailers.smtp.encryption') . "
- Timestamp: " . now()->format('Y-m-d H:i:s') . "

If you receive this email, your MailerSend SMTP broadcast system is working correctly!";
            
            $this->info('Sending broadcast...');
            
            // Send broadcast
            $result = $mailerSendService->sendBroadcast($subject, $body, $recipients);
            
            if ($result['success']) {
                $this->info('✅ Broadcast sent successfully!');
                $this->newLine();
                $this->table(
                    ['Stat', 'Value'],
                    [
                        ['Total Recipients', $result['stats']['total_users']],
                        ['Successfully Sent', $result['stats']['sent_count']],
                        ['Failed', $result['stats']['failed_count']],
                    ]
                );
                $this->newLine();
                $this->info('Please check your inbox at: ' . $testEmail);
                $this->info('If you don\'t see the email, check your spam folder.');
                
                return Command::SUCCESS;
            } else {
                $this->error('❌ Broadcast failed!');
                $this->error('Error: ' . ($result['error'] ?? 'Unknown error'));
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error occurred!');
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

