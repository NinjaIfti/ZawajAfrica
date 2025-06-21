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
            'timeout' => null,
        ]);

        Config::set('mail.from', [
            'address' => $this->fromAddress,
            'name' => $this->fromName
        ]);

        Log::info('Zoho Mail SMTP configured successfully');
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