<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MailerSendService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    private MailerSendService $mailerSendService;

    public function __construct(MailerSendService $mailerSendService)
    {
        $this->mailerSendService = $mailerSendService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            // For security, don't reveal whether email exists
            return back()->with('status', 'We have emailed your password reset link if the email exists in our system.');
        }

        try {
            // Generate password reset token
            $token = Str::random(60);
            
            // Store token in password_reset_tokens table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $token,
                    'created_at' => now()
                ]
            );

            // Create reset URL
            $resetUrl = url(route('password.reset', ['token' => $token], false)) . '?email=' . urlencode($email);

            // Send password reset email via MailerSend
            $result = $this->mailerSendService->sendPasswordReset($email, $resetUrl, $user->name);

            if ($result['success']) {
                Log::info('Password reset email sent successfully', [
                    'email' => $email,
                    'user_id' => $user->id,
                    'message_id' => $result['message_id'] ?? null
                ]);

                return back()->with('status', 'We have emailed your password reset link!');
            } else {
                Log::error('Failed to send password reset email', [
                    'email' => $email,
                    'user_id' => $user->id,
                    'error' => $result['error']
                ]);

                throw ValidationException::withMessages([
                    'email' => ['Unable to send password reset email. Please try again later.'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Password reset process failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            throw ValidationException::withMessages([
                'email' => ['An error occurred while processing your request. Please try again.'],
            ]);
        }
    }
}
