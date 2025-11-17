<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailerSendSMTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-mailersend {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MailerSend SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing MailerSend SMTP Configuration...');
        $this->newLine();
        
        // Display configuration
        $this->info('Configuration:');
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
        $this->info('Testing SMTP connection...');
        
        try {
            // Get test email address
            $testEmail = $this->argument('email') ?? config('mail.from.address');
            
            $this->info("Sending test email to: {$testEmail}");
            $this->info('Attempting to connect to SMTP server...');
            
            // Send test email using Laravel Mail facade
            Mail::raw('This is a test email from ZawajAfrica MailerSend SMTP configuration.

Test Details:
- Server: ' . config('mail.mailers.smtp.host') . '
- Port: ' . config('mail.mailers.smtp.port') . '
- Encryption: ' . config('mail.mailers.smtp.encryption') . '
- Timestamp: ' . now()->format('Y-m-d H:i:s') . '

If you receive this email, your MailerSend SMTP configuration is working correctly!', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('MailerSend SMTP Test - ' . now()->format('Y-m-d H:i:s'));
            });
            
            $this->info('✅ Test email sent successfully!');
            $this->newLine();
            $this->info('Please check your inbox at: ' . $testEmail);
            $this->info('If you don\'t see the email, check your spam folder.');
            
            return Command::SUCCESS;
            
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            $this->error('❌ SMTP Connection Failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Common issues:');
            $this->line('1. Check if credentials are correct');
            $this->line('2. Verify port 587 is not blocked by firewall');
            $this->line('3. Ensure TLS is enabled');
            $this->line('4. Check if MailerSend account is active');
            
            Log::error('MailerSend SMTP Test Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
            
        } catch (\Exception $e) {
            $this->error('❌ Error occurred!');
            $this->error('Error: ' . $e->getMessage());
            
            Log::error('MailerSend SMTP Test Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
