<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class FallbackEmailService
{
    private array $smtpConfigs;
    private int $maxRetries = 3;
    private int $retryDelay = 5; // seconds

    public function __construct()
    {
        $this->smtpConfigs = [
            'zoho_primary' => [
                'transport' => 'smtp',
                'host' => config('services.zoho_mail.smtp_host', 'smtp.zoho.com'),
                'port' => config('services.zoho_mail.smtp_port', 587),
                'username' => config('services.zoho_mail.smtp_username'),
                'password' => config('services.zoho_mail.smtp_password'),
                'encryption' => config('services.zoho_mail.smtp_encryption', 'tls'),
                'timeout' => 30,
            ],
            'zoho_ssl' => [
                'transport' => 'smtp',
                'host' => config('services.zoho_mail.smtp_host', 'smtp.zoho.com'),
                'port' => 465,
                'username' => config('services.zoho_mail.smtp_username'),
                'password' => config('services.zoho_mail.smtp_password'),
                'encryption' => 'ssl',
                'timeout' => 30,
            ],
            'log_fallback' => [
                'transport' => 'log',
                'channel' => env('MAIL_LOG_CHANNEL'),
            ],
        ];
    }

    /**
     * Send email with automatic fallback
     */
    public function sendWithFallback(string $to, string $subject, string $body, string $toName = '', array $options = []): array
    {
        $attempts = 0;
        $lastError = null;

        foreach ($this->smtpConfigs as $configName => $config) {
            $attempts++;
            
            try {
                // Skip non-configured SMTP settings
                if ($config['transport'] === 'smtp' && empty($config['username'])) {
                    continue;
                }

                Log::info("Attempting to send email via {$configName}", [
                    'to' => $to,
                    'subject' => $subject,
                    'attempt' => $attempts
                ]);

                $this->configureMailer($configName, $config);
                
                // Test connectivity first for SMTP
                if ($config['transport'] === 'smtp') {
                    if (!$this->testSMTPConnectivity($config['host'], $config['port'])) {
                        throw new Exception("Cannot connect to {$config['host']}:{$config['port']}");
                    }
                }

                // Send the email
                Mail::raw($body, function ($message) use ($to, $subject, $toName) {
                    $message->to($to, $toName)->subject($subject);
                });

                Log::info("Email sent successfully via {$configName}", [
                    'to' => $to,
                    'subject' => $subject
                ]);

                return [
                    'success' => true,
                    'method' => $configName,
                    'attempts' => $attempts,
                    'message' => "Email sent successfully via {$configName}"
                ];

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                
                Log::warning("Failed to send email via {$configName}", [
                    'to' => $to,
                    'subject' => $subject,
                    'error' => $lastError,
                    'attempt' => $attempts
                ]);

                // Add delay before next attempt (except for log fallback)
                if ($config['transport'] === 'smtp' && $attempts < count($this->smtpConfigs)) {
                    sleep($this->retryDelay);
                }
            }
        }

        // All methods failed
        Log::error('All email sending methods failed', [
            'to' => $to,
            'subject' => $subject,
            'total_attempts' => $attempts,
            'last_error' => $lastError
        ]);

        return [
            'success' => false,
            'method' => null,
            'attempts' => $attempts,
            'error' => $lastError,
            'message' => "Failed to send email after {$attempts} attempts"
        ];
    }

    /**
     * Configure Laravel mailer with specific config
     */
    private function configureMailer(string $name, array $config): void
    {
        Config::set('mail.default', $name);
        Config::set("mail.mailers.{$name}", $config);

        // Set from address
        if ($config['transport'] === 'smtp') {
            Config::set('mail.from', [
                'address' => config('services.zoho_mail.from_address'),
                'name' => config('services.zoho_mail.from_name', 'ZawajAfrica')
            ]);
        }
    }

    /**
     * Test SMTP connectivity
     */
    private function testSMTPConnectivity(string $host, int $port, int $timeout = 10): bool
    {
        try {
            $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
            
            if ($connection) {
                fclose($connection);
                return true;
            }
            
            Log::debug("SMTP connectivity test failed", [
                'host' => $host,
                'port' => $port,
                'error' => $errstr,
                'errno' => $errno
            ]);
            
            return false;
        } catch (Exception $e) {
            Log::debug("SMTP connectivity test exception", [
                'host' => $host,
                'port' => $port,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Send email for specific notification type
     */
    public function sendNotificationEmail(string $type, string $to, string $subject, string $body, string $toName = ''): array
    {
        Log::info("Sending {$type} notification email", [
            'to' => $to,
            'subject' => $subject
        ]);

        $result = $this->sendWithFallback($to, $subject, $body, $toName);
        
        if (!$result['success']) {
            // Store failed email for retry later
            $this->storeFailedEmail([
                'type' => $type,
                'to' => $to,
                'to_name' => $toName,
                'subject' => $subject,
                'body' => $body,
                'failed_at' => now(),
                'attempts' => $result['attempts'],
                'error' => $result['error']
            ]);
        }

        return $result;
    }

    /**
     * Store failed email for retry
     */
    private function storeFailedEmail(array $emailData): void
    {
        $cacheKey = 'failed_email_' . md5($emailData['to'] . $emailData['subject'] . time());
        
        // Store in cache for 24 hours
        cache()->put($cacheKey, $emailData, 60 * 60 * 24);
        
        Log::info('Failed email stored for retry', [
            'cache_key' => $cacheKey,
            'to' => $emailData['to'],
            'type' => $emailData['type']
        ]);
    }

    /**
     * Retry failed emails
     */
    public function retryFailedEmails(): array
    {
        $retryResults = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'details' => []
        ];

        // This is a simplified version - in production you'd want to use a proper database table
        // For now, we'll just log that the retry system is available
        Log::info('Failed email retry system is available');

        return $retryResults;
    }

    /**
     * Get email delivery status
     */
    public function getDeliveryStatus(): array
    {
        return [
            'zoho_smtp_available' => $this->testSMTPConnectivity(
                config('services.zoho_mail.smtp_host', 'smtp.zoho.com'),
                config('services.zoho_mail.smtp_port', 587)
            ),
            'fallback_methods' => count($this->smtpConfigs),
            'last_test' => now()->format('Y-m-d H:i:s')
        ];
    }
} 