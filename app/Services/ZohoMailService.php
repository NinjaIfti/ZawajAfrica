<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\ZohoHttpEmailService;

class ZohoMailService
{
    private ZohoHttpEmailService $httpEmailService;

    public function __construct()
    {
        $this->httpEmailService = app(ZohoHttpEmailService::class);
    }

    /**
     * Check if Zoho Mail is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->httpEmailService->isConfigured();
    }

    /**
     * Configure mailer (no longer needed, kept for compatibility)
     */
    public function configureMailer(): void
    {
        Log::debug('ZohoMailService: Using HTTP API, no SMTP configuration needed');
    }

    /**
     * Configure mailer with specific sender (no longer needed, kept for compatibility)
     */
    public function configureMailerWithSender(string $senderType = 'default'): void
    {
        Log::debug("ZohoMailService: Using HTTP API for {$senderType} sender");
    }

    /**
     * Send email with specific sender type (now uses HTTP API only)
     */
    public function sendWithSender(string $senderType, $mailable, $to): bool
    {
        try {
            // Extract email content from mailable
            $emailAddress = is_string($to) ? $to : $to->email;
            $userName = is_string($to) ? '' : ($to->name ?? '');
            
            // Render the mailable to get subject and body
            $rendered = $mailable->render();
            $subject = $mailable->subject ?? 'ZawajAfrica Notification';
            
            $result = $this->httpEmailService->sendNotificationEmail(
                $senderType,
                $emailAddress,
                $subject,
                $rendered,
                $userName
            );
            
            if ($result['success']) {
                Log::info("Email sent successfully via HTTP API with {$senderType} sender", [
                    'to' => $emailAddress,
                    'subject' => $subject
                ]);
                return true;
            } else {
                Log::error("Failed to send email via HTTP API with {$senderType} sender", [
                    'error' => $result['error'],
                    'to' => $emailAddress,
                    'subject' => $subject
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("HTTP API email service failed", [
                'error' => $e->getMessage(),
                'to' => is_string($to) ? $to : $to->email ?? 'unknown',
                'sender_type' => $senderType
            ]);
            return false;
        }
    }

    /**
     * Send therapist-related email
     */
    public function sendTherapistEmail($mailable, $to): bool
    {
        return $this->sendWithSender('therapist', $mailable, $to);
    }

    /**
     * Send admin email
     */
    public function sendAdminEmail($mailable, $to): bool
    {
        return $this->sendWithSender('admin', $mailable, $to);
    }

    /**
     * Send support email
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
     * Test email connectivity (now uses HTTP API)
     */
    public function testConnection(): array
    {
        return $this->httpEmailService->testConnection();
    }

    /**
     * Get Zoho Mail configuration status
     */
    public function getStatus(): array
    {
        $httpStatus = $this->httpEmailService->getStatus();
        
        return [
            'configured' => $httpStatus['configured'],
            'method' => 'HTTP API',
            'api_url' => $httpStatus['api_url'],
            'from_address' => $httpStatus['from_email'],
            'from_name' => $httpStatus['from_name'],
            'has_token' => $httpStatus['has_token']
        ];
    }

    /**
     * Send broadcast emails
     */
    public function sendBroadcast(string $subject, string $body, array $recipients, string $senderType = 'admin'): array
    {
        return $this->httpEmailService->sendBroadcast($subject, $body, $recipients, $senderType);
    }
} 