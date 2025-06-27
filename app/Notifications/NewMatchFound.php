<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Services\ZohoMailService;
use App\Traits\ZohoMailTemplate;

class NewMatchFound extends Notification implements ShouldQueue // Implement ShouldQueue for async processing
{
    use Queueable, ZohoMailTemplate;

    private User $match;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $match)
    {
        $this->match = $match;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        // Send database notification immediately, queue email for later
        return ['database'];
    }

    /**
     * Determine the time at which the email should be sent.
     */
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    /**
     * Queue the email notification separately with delay
     */
    public function sendDelayedEmail($notifiable)
    {
        // Queue email notification with 30 second delay
        dispatch(function () use ($notifiable) {
            try {
                $this->sendEmailNotification($notifiable);
            } catch (\Exception $e) {
                \Log::error('Failed to send delayed match email', [
                    'match_id' => $this->match->id,
                    'receiver_id' => $notifiable->id,
                    'error' => $e->getMessage()
                ]);
            }
        })->delay(now()->addSeconds(30));
    }

    /**
     * Send email notification separately
     */
    private function sendEmailNotification($notifiable)
    {
        try {
            // Configure Zoho Mail before sending
            $zohoMailService = app(ZohoMailService::class);
            $zohoMailService->configureMailer();

            $subject = '🌟 It\'s a Match! You and ' . $this->match->name . ' liked each other!';
            
            $content = "Hi " . $notifiable->name . ",\n\n" .
                      "🎉 Congratulations! You have a new match!\n\n" .
                      "You and " . $this->match->name . " both liked each other!\n\n" .
                      "This means you can now start messaging each other and get to know one another better.\n\n" .
                      "Start messaging: " . url('/messages') . "\n\n" .
                      "Next Steps:\n" .
                      "• Send a thoughtful first message\n" .
                      "• Be genuine and respectful in your conversations\n" .
                      "• Take your time to get to know each other\n\n" .
                      "We're excited to see where this connection leads!\n\n" .
                      "Best regards,\nZawajAfrica Team";

            // Send simple text email
            \Mail::raw($content, function ($message) use ($notifiable, $subject) {
                $message->to($notifiable->email)
                        ->subject($subject);
            });

            \Log::info('Match email sent successfully (delayed)', [
                'match_id' => $this->match->id,
                'receiver_id' => $notifiable->id,
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send match email', [
                'match_id' => $this->match->id,
                'receiver_id' => $notifiable->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Match Found! 🎉',
            'message' => 'You have a new match with ' . $this->match->name . '!',
            'icon' => 'heart',
            'color' => 'green',
            'action_text' => 'View Match',
            'action_url' => route('messages'),
            'match_id' => $this->match->id,
            'match_name' => $this->match->name,
            'match_photo' => $this->match->profile_photo ? asset('storage/' . $this->match->profile_photo) : null
        ];
    }
}
