<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MailerSendService
{
    private string $apiKey;
    private string $apiUrl;
    private string $fromEmail;
    private string $fromName;
    private bool $enabled;

    public function __construct()
    {
        $this->apiKey = config('services.mailersend.api_key');
        $this->apiUrl = config('services.mailersend.api_url');
        $this->fromEmail = config('services.mailersend.from_email');
        $this->fromName = config('services.mailersend.from_name');
        $this->enabled = config('services.mailersend.enabled', false);
    }

    /**
     * Check if MailerSend is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->apiKey) && !empty($this->fromEmail);
    }

    /**
     * Send OTP verification email
     */
    public function sendOTP(string $to, string $otp, string $toName = ''): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'MailerSend not properly configured'
            ];
        }

        $subject = 'ZawajAfrica OTP Verification';
        $htmlContent = $this->generateOTPEmailContent($otp, $toName);
        $textContent = "ZawajAfrica OTP: {$otp}. This code expires in 10 minutes.";

        return $this->sendEmail($to, $toName, $subject, $htmlContent, $textContent);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(string $to, string $resetUrl, string $toName = ''): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'MailerSend not properly configured'
            ];
        }

        $subject = 'ZawajAfrica – Reset Your Password';
        $htmlContent = $this->generatePasswordResetEmailContent($resetUrl, $toName);
        $textContent = "Click the link below to reset your password: {$resetUrl}. If you didn't request this, you can ignore it.";

        return $this->sendEmail($to, $toName, $subject, $htmlContent, $textContent);
    }

    /**
     * Send email via MailerSend API
     */
    private function sendEmail(string $to, string $toName, string $subject, string $htmlContent, string $textContent): array
    {
        try {
            $payload = [
                'from' => [
                    'email' => $this->fromEmail,
                    'name' => $this->fromName
                ],
                'to' => [
                    [
                        'email' => $to,
                        'name' => $toName ?: $to
                    ]
                ],
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent
            ];

            Log::info('Sending email via MailerSend', [
                'to' => $to,
                'subject' => $subject
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ])->timeout(30)->post($this->apiUrl . '/email', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Email sent successfully via MailerSend', [
                    'to' => $to,
                    'subject' => $subject,
                    'message_id' => $responseData['message_id'] ?? 'unknown'
                ]);

                return [
                    'success' => true,
                    'message_id' => $responseData['message_id'] ?? null,
                    'response' => $responseData
                ];
            } else {
                $error = $response->json()['message'] ?? 'Unknown error';
                
                Log::error('MailerSend API email failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'error' => $error,
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => "MailerSend API Error: {$error}",
                    'status_code' => $response->status()
                ];
            }

        } catch (Exception $e) {
            Log::error('MailerSend email exception', [
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
     * Generate OTP email HTML content
     */
    private function generateOTPEmailContent(string $otp, string $toName): string
    {
        $greeting = $toName ? "Salam Alaikum {$toName}!" : "Salam Alaikum!";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>ZawajAfrica OTP Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #654396, #8B5A9C); padding: 30px; text-align: center; color: white; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-code { background: #fff; border: 2px solid #654396; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .otp-number { font-size: 32px; font-weight: bold; color: #654396; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 OTP Verification</h1>
                    <p>ZawajAfrica Security Code</p>
                </div>
                <div class='content'>
                    <h2>{$greeting}</h2>
                    <p>You requested a verification code for your ZawajAfrica account. Please use the following OTP to complete your verification:</p>
                    
                    <div class='otp-code'>
                        <div class='otp-number'>{$otp}</div>
                        <p style='margin: 10px 0 0 0; color: #666;'>This code expires in 10 minutes</p>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Important Security Notice:</strong>
                        <ul style='margin: 10px 0;'>
                            <li>Never share this code with anyone</li>
                            <li>ZawajAfrica will never ask for your OTP via phone or email</li>
                            <li>If you didn't request this code, please ignore this email</li>
                        </ul>
                    </div>
                    
                    <p>If you're having trouble with verification, please contact our support team at <a href='mailto:support@zawajafrica.com.ng'>support@zawajafrica.com.ng</a></p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " ZawajAfrica. Connecting African Muslims worldwide.</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Generate password reset email HTML content
     */
    private function generatePasswordResetEmailContent(string $resetUrl, string $toName): string
    {
        $greeting = $toName ? "Salam Alaikum {$toName}!" : "Salam Alaikum!";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>ZawajAfrica Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #654396, #8B5A9C); padding: 30px; text-align: center; color: white; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .reset-button { text-align: center; margin: 30px 0; }
                .reset-button a { background: #654396; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
                .reset-button a:hover { background: #523380; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
                .security-notice { background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔑 Password Reset</h1>
                    <p>ZawajAfrica Account Security</p>
                </div>
                <div class='content'>
                    <h2>{$greeting}</h2>
                    <p>We received a request to reset your ZawajAfrica account password. Click the button below to create a new password:</p>
                    
                    <div class='reset-button'>
                        <a href='{$resetUrl}'>Reset My Password</a>
                    </div>
                    
                    <div class='security-notice'>
                        <strong>🛡️ Security Information:</strong>
                        <ul style='margin: 10px 0;'>
                            <li>This link will expire in 60 minutes for your security</li>
                            <li>If you didn't request this reset, you can safely ignore this email</li>
                            <li>Your current password will remain unchanged until you create a new one</li>
                        </ul>
                    </div>
                    
                    <p><strong>Can't click the button?</strong> Copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px;'>{$resetUrl}</p>
                    
                    <p>If you're having trouble resetting your password, please contact our support team at <a href='mailto:support@zawajafrica.com.ng'>support@zawajafrica.com.ng</a></p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " ZawajAfrica. Connecting African Muslims worldwide.</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Test MailerSend connection
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'MailerSend not configured'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(10)->get($this->apiUrl . '/me');

            if ($response->successful()) {
                Log::info('MailerSend connection test successful');
                
                return [
                    'success' => true,
                    'message' => 'MailerSend connection successful',
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'MailerSend API test failed: ' . $response->body()
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
            'enabled' => $this->enabled,
            'api_url' => $this->apiUrl,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'has_api_key' => !empty($this->apiKey)
        ];
    }
} 