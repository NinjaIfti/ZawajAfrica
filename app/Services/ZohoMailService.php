<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ZohoMailService
{
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpEncryption;
    private $fromAddress;
    private $fromName;

    public function __construct()
    {
        $this->smtpHost = config('services.zoho_mail.smtp_host');
        $this->smtpPort = config('services.zoho_mail.smtp_port');
        $this->smtpUsername = config('services.zoho_mail.smtp_username');
        $this->smtpPassword = config('services.zoho_mail.smtp_password');
        $this->smtpEncryption = config('services.zoho_mail.smtp_encryption');
        $this->fromAddress = config('services.zoho_mail.from_address');
        $this->fromName = config('services.zoho_mail.from_name');
    }

    /**
     * Check if Zoho Mail is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->smtpHost) && 
               !empty($this->smtpPort) && 
               !empty($this->smtpUsername) && 
               !empty($this->smtpPassword) && 
               !empty($this->fromAddress);
    }

    /**
     * Configure Laravel Mail to use Zoho SMTP
     */
    public function configureMailer(): void
    {
        if (!$this->isConfigured()) {
            Log::warning('Zoho Mail not properly configured, using default mailer');
            return;
        }

        // Dynamically configure mail settings
        Config::set('mail.default', 'zoho_smtp');
        Config::set('mail.mailers.zoho_smtp', [
            'transport' => 'smtp',
            'host' => $this->smtpHost,
            'port' => $this->smtpPort,
            'username' => $this->smtpUsername,
            'password' => $this->smtpPassword,
            'encryption' => $this->smtpEncryption,
            'timeout' => config('mail.mailers.smtp.timeout', 120),
        ]);

        Config::set('mail.from', [
            'address' => $this->fromAddress,
            'name' => $this->fromName
        ]);

        Log::info('Zoho Mail SMTP configured successfully');
    }

    /**
     * Configure Laravel Mail to use specific sender address
     */
    public function configureMailerWithSender(string $senderType = 'default'): void
    {
        $this->configureMailer();
        
        // Override from address based on sender type
        $addresses = config('services.zoho_mail.addresses', []);
        
        if (isset($addresses[$senderType])) {
            Config::set('mail.from', [
                'address' => $addresses[$senderType]['address'],
                'name' => $addresses[$senderType]['name']
            ]);
            
            Log::info("Mail configured with {$senderType} sender", [
                'address' => $addresses[$senderType]['address'],
                'name' => $addresses[$senderType]['name']
            ]);
        }
    }

    /**
     * Send email with specific sender type
     */
    public function sendWithSender(string $senderType, $mailable, $to): bool
    {
        try {
            $this->configureMailerWithSender($senderType);
            Mail::to($to)->send($mailable);
            
            Log::info("Email sent successfully with {$senderType} sender", [
                'to' => is_string($to) ? $to : $to->email ?? 'unknown',
                'mailable' => get_class($mailable)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email with {$senderType} sender", [
                'error' => $e->getMessage(),
                'to' => is_string($to) ? $to : $to->email ?? 'unknown',
                'mailable' => get_class($mailable)
            ]);
            
            return false;
        }
    }

    /**
     * Send therapist-related email from support address
     */
    public function sendTherapistEmail($mailable, $to): bool
    {
        return $this->sendWithSender('therapist', $mailable, $to);
    }

    /**
     * Send admin email from admin address
     */
    public function sendAdminEmail($mailable, $to): bool
    {
        return $this->sendWithSender('admin', $mailable, $to);
    }

    /**
     * Send support email from support address
     */
    public function sendSupportEmail($mailable, $to): bool
    {
        return $this->sendWithSender('support', $mailable, $to);
    }

    /**
     * Send no-reply email
     */
    public function sendNoReplyEmail($mailable, $to): bool
    {
        return $this->sendWithSender('noreply', $mailable, $to);
    }

    /**
     * Test email connectivity
     */
    public function testConnection(): array
    {
        try {
            $this->configureMailer();
            
            // Test email content
            $testData = [
                'subject' => 'ZawajAfrica - Mail Configuration Test',
                'body' => 'This is a test email to verify Zoho Mail SMTP configuration is working properly.',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            Mail::raw($testData['body'], function ($message) use ($testData) {
                $message->to($this->smtpUsername)
                       ->subject($testData['subject']);
            });

            Log::info('Zoho Mail test email sent successfully');
            
            return [
                'status' => true,
                'message' => 'Test email sent successfully via Zoho Mail'
            ];

        } catch (\Exception $e) {
            Log::error('Zoho Mail test failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return [
                'status' => false,
                'message' => 'Test email failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Zoho Mail configuration status
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'smtp_host' => $this->smtpHost,
            'smtp_port' => $this->smtpPort,
            'smtp_username' => $this->smtpUsername ? '***@' . substr($this->smtpUsername, strpos($this->smtpUsername, '@')) : null,
            'from_address' => $this->fromAddress,
            'from_name' => $this->fromName
        ];
    }

    /**
     * Get recommended email addresses for ZawajAfrica
     */
    public function getRecommendedEmailAddresses(): array
    {
        $domain = '@zawajafrica.online';
        
        return [
            'support' => 'support' . $domain,
            'bookings' => 'bookings' . $domain,
            'noreply' => 'noreply' . $domain,
            'info' => 'info' . $domain,
            'admin' => 'admin' . $domain,
            'notifications' => 'notifications' . $domain
        ];
    }
} 