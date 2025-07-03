<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Services\MailerSendService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    private MailerSendService $mailerSendService;

    public function __construct(MailerSendService $mailerSendService)
    {
        $this->mailerSendService = $mailerSendService;
    }

    /**
     * Send OTP for verification
     */
    public function sendOTP(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'purpose' => 'required|in:login,signup,password_reset,email_verification'
        ]);

        $email = $request->email;
        $purpose = $request->purpose;

        // Rate limiting
        $key = 'otp-send:' . $email . ':' . $purpose;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        // Check if user can generate OTP (rate limiting)
        if (!OtpVerification::canGenerateOTP($email, $purpose)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please try again later.'
            ], 429);
        }

        try {
            // Generate OTP
            $otp = OtpVerification::generateOTP($email, $purpose);

            // Send OTP via MailerSend
            $result = $this->mailerSendService->sendOTP($email, $otp);

            if ($result['success']) {
                Log::info('OTP sent successfully', [
                    'email' => $email,
                    'purpose' => $purpose,
                    'message_id' => $result['message_id'] ?? null
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent successfully. Please check your email.',
                    'expires_in' => 600 // 10 minutes in seconds
                ]);
            } else {
                Log::error('Failed to send OTP email', [
                    'email' => $email,
                    'purpose' => $purpose,
                    'error' => $result['error']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('OTP generation failed', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'purpose' => 'required|in:login,signup,password_reset,email_verification'
        ]);

        $email = $request->email;
        $otp = $request->otp;
        $purpose = $request->purpose;

        // Rate limiting for verification attempts
        $key = 'otp-verify:' . $email . ':' . $purpose;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many verification attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        try {
            $isValid = OtpVerification::verifyOTP($email, $otp, $purpose);

            if ($isValid) {
                Log::info('OTP verified successfully', [
                    'email' => $email,
                    'purpose' => $purpose
                ]);

                // Clear rate limit on successful verification
                RateLimiter::clear($key);

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully.'
                ]);
            } else {
                Log::warning('Invalid OTP verification attempt', [
                    'email' => $email,
                    'purpose' => $purpose,
                    'provided_otp' => $otp
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please try again.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('OTP verification failed', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification. Please try again.'
            ], 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOTP(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'purpose' => 'required|in:login,signup,password_reset,email_verification'
        ]);

        // Use the same logic as sendOTP but with a different rate limit key
        $key = 'otp-resend:' . $request->email . ':' . $request->purpose;
        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Please wait {$seconds} seconds before requesting another OTP.",
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 600); // 10 minutes

        return $this->sendOTP($request);
    }

    /**
     * Check OTP status (for frontend polling)
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'purpose' => 'required|in:login,signup,password_reset,email_verification'
        ]);

        $email = $request->email;
        $purpose = $request->purpose;

        $otpRecord = OtpVerification::where('email', $email)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($otpRecord) {
            return response()->json([
                'success' => true,
                'has_pending_otp' => true,
                'expires_in' => $otpRecord->remaining_time,
                'created_at' => $otpRecord->created_at->toISOString()
            ]);
        }

        return response()->json([
            'success' => true,
            'has_pending_otp' => false
        ]);
    }
}
