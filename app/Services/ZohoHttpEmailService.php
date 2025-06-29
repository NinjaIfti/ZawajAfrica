<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ZohoHttpEmailService
{
    private string $apiUrl;
    private ?string $accessToken;
    private ?string $accountId;
    private ?string $fromEmail;
    private string $fromName;
    private array $headers;

    public function __construct()
    {
        $this->apiUrl = 'https://mail.zoho.com/api';
        $this->accessToken = config('services.zoho_mail.api_token');
        $this->accountId = config('services.zoho_mail.account_id');
        $this->fromEmail = config('services.zoho_mail.from_address');
        $this->fromName = config('services.zoho_mail.from_name', 'ZawajAfrica');
        
        $this->headers = [
            'Authorization' => 'Zoho-oauthtoken ' . ($this->accessToken ?? ''),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Check if Zoho HTTP Email is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && !empty($this->accountId) && !empty($this->fromEmail);
    }

    /**
     * Send email using Zoho HTTP API
     */
    public function sendEmail(string $to, string $subject, string $body, string $toName = '', array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Zoho HTTP Email not properly configured'
            ];
        }

        try {
            $payload = $this->buildEmailPayload($to, $subject, $body, $toName, $options);
            
            Log::info('Sending email via Zoho HTTP API', [
                'to' => $to,
                'subject' => $subject
            ]);

            $response = Http::withHeaders($this->headers)
                          ->timeout(30)
                          ->post($this->apiUrl . '/accounts/' . $this->accountId . '/messages', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Email sent successfully via Zoho HTTP API', [
                    'to' => $to,
                    'subject' => $subject,
                    'message_id' => $responseData['messageId'] ?? 'unknown'
                ]);

                return [
                    'success' => true,
                    'message_id' => $responseData['messageId'] ?? null,
                    'response' => $responseData
                ];
            } else {
                $error = $response->json()['error'] ?? 'Unknown error';
                
                Log::error('Zoho HTTP API email failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'error' => $error,
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => "Zoho API Error: {$error}",
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            Log::error('Zoho HTTP email exception', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build email payload for Zoho API
     */
    private function buildEmailPayload(string $to, string $subject, string $body, string $toName = '', array $options = []): array
    {
        $isPlainText = $options['content_type'] === 'text/plain';
        $mailFormat = $isPlainText ? 'plaintext' : 'html';
        
        // If sending as HTML but content appears to be plain text, convert it
        if (!$isPlainText && !$this->isHtmlContent($body)) {
            $body = $this->convertPlainTextToHtml($body);
        }
        
        $payload = [
            'fromAddress' => $this->fromEmail,
            'toAddress' => $to,
            'subject' => $subject,
            'content' => $body,
            'mailFormat' => $mailFormat
        ];

        // Add CC if provided
        if (isset($options['cc'])) {
            $payload['ccAddress'] = $options['cc'];
        }

        // Add BCC if provided
        if (isset($options['bcc'])) {
            $payload['bccAddress'] = $options['bcc'];
        }

        return $payload;
    }

    /**
     * Check if content contains HTML tags
     */
    private function isHtmlContent(string $content): bool
    {
        return $content !== strip_tags($content);
    }

    /**
     * Convert plain text to HTML formatting
     */
    private function convertPlainTextToHtml(string $text): string
    {
        // First, escape any existing HTML to prevent issues
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        // Convert double line breaks to paragraph breaks
        $text = preg_replace('/\n\s*\n/', '</p><p>', $text);
        
        // Convert single line breaks to <br> tags
        $text = nl2br($text);
        
        // Wrap the entire content in <p> tags
        $text = '<p>' . $text . '</p>';
        
        // Clean up empty paragraphs
        $text = preg_replace('/<p>\s*<\/p>/', '', $text);
        
        // Fix any double paragraph tags
        $text = preg_replace('/<\/p><p>/', '</p><p>', $text);
        
        return $text;
    }

    /**
     * Send notification email with specific sender type
     */
    public function sendNotificationEmail(string $type, string $to, string $subject, string $body, string $toName = ''): array
    {
        // Get sender configuration based on type
        $senderConfig = $this->getSenderConfig($type);
        
        $options = [
            'content_type' => 'text/html',
            'reply_to' => $senderConfig['reply_to'] ?? $this->fromEmail
        ];

        // Override from email/name if specific sender type
        if ($senderConfig) {
            $originalFromEmail = $this->fromEmail;
            $originalFromName = $this->fromName;
            
            $this->fromEmail = $senderConfig['email'];
            $this->fromName = $senderConfig['name'];
            
            $result = $this->sendEmail($to, $subject, $body, $toName, $options);
            
            // Restore original settings
            $this->fromEmail = $originalFromEmail;
            $this->fromName = $originalFromName;
            
            return $result;
        }

        return $this->sendEmail($to, $subject, $body, $toName, $options);
    }

    /**
     * Get sender configuration based on type
     */
    private function getSenderConfig(string $type): ?array
    {
        $senderConfigs = [
            'support' => [
                'email' => config('services.zoho_mail.addresses.support.address'),
                'name' => config('services.zoho_mail.addresses.support.name'),
                'reply_to' => config('services.zoho_mail.addresses.support.address')
            ],
            'admin' => [
                'email' => config('services.zoho_mail.addresses.admin.address'),
                'name' => config('services.zoho_mail.addresses.admin.name'),
                'reply_to' => config('services.zoho_mail.addresses.admin.address')
            ],
            'therapist' => [
                'email' => config('services.zoho_mail.addresses.therapist.address'),
                'name' => config('services.zoho_mail.addresses.therapist.name'),
                'reply_to' => config('services.zoho_mail.addresses.therapist.address')
            ],
            'noreply' => [
                'email' => config('services.zoho_mail.addresses.noreply.address'),
                'name' => config('services.zoho_mail.addresses.noreply.name'),
                'reply_to' => null // No reply for noreply emails
            ]
        ];

        return $senderConfigs[$type] ?? null;
    }

    /**
     * Test connection to Zoho HTTP API
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Zoho HTTP Email not configured'
            ];
        }

        try {
            // Test with a simple API call to check authentication
            $response = Http::withHeaders($this->headers)
                          ->timeout(10)
                          ->get($this->apiUrl . '/accounts');

            if ($response->successful()) {
                Log::info('Zoho HTTP API connection test successful');
                
                return [
                    'success' => true,
                    'message' => 'Zoho HTTP API connection successful'
                ];
            } else {
                $error = $response->json()['error'] ?? 'Authentication failed';
                
                return [
                    'success' => false,
                    'error' => "Zoho HTTP API test failed: {$error}"
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get service status
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'api_url' => $this->apiUrl,
            'account_id' => $this->accountId,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'has_token' => !empty($this->accessToken),
            'has_account_id' => !empty($this->accountId)
        ];
    }

    /**
     * Send broadcast email to multiple recipients
     */
    public function sendBroadcast(string $subject, string $body, array $recipients, string $senderType = 'admin'): array
    {
        $results = [
            'total' => count($recipients),
            'successful' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($recipients as $recipient) {
            $email = is_array($recipient) ? $recipient['email'] : $recipient;
            $name = is_array($recipient) ? ($recipient['name'] ?? '') : '';

            $result = $this->sendNotificationEmail($senderType, $email, $subject, $body, $name);
            
            if ($result['success']) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'email' => $email,
                'success' => $result['success'],
                'error' => $result['error'] ?? null
            ];

            // Small delay to prevent rate limiting
            usleep(200000); // 0.2 seconds
        }

        Log::info('Zoho HTTP broadcast completed', [
            'total' => $results['total'],
            'successful' => $results['successful'],
            'failed' => $results['failed']
        ]);

        return $results;
    }
} 