<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\AIEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    private AIEmailService $aiEmailService;

    /**
     * Create the event listener.
     */
    public function __construct(AIEmailService $aiEmailService)
    {
        $this->aiEmailService = $aiEmailService;
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            Log::info('Sending welcome email to new user', [
                'user_id' => $event->user->id,
                'email' => $event->user->email
            ]);

            $result = $this->aiEmailService->sendWelcomeEmail($event->user);

            if ($result['success']) {
                Log::info('Welcome email sent successfully', [
                    'user_id' => $event->user->id
                ]);
            } else {
                Log::error('Failed to send welcome email', [
                    'user_id' => $event->user->id,
                    'error' => $result['error']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception in welcome email listener', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::error('Welcome email job failed permanently', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage()
        ]);
    }
} 