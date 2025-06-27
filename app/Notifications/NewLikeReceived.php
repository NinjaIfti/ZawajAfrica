<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Services\ZohoMailService;
use App\Services\UserTierService;
use App\Traits\ZohoMailTemplate;

class NewLikeReceived extends Notification implements ShouldQueue // Implement ShouldQueue for async processing
{
    use Queueable, ZohoMailTemplate;

    private User $liker;
    private User $receiver;
    private string $receiverTier;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $liker, User $receiver)
    {
        $this->liker = $liker;
        $this->receiver = $receiver;
        
        // Determine receiver's tier to decide if we can reveal liker's name
        $tierService = app(UserTierService::class);
        $this->receiverTier = $tierService->getUserTier($receiver);
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
                \Log::error('Failed to send delayed like email', [
                    'liker_id' => $this->liker->id,
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

            $canReveal = $this->canRevealLiker();
            
            if ($canReveal) {
                // For paid users - show actual liker's name
                $subject = '💕 ' . $this->liker->name . ' likes you on ZawajAfrica!';
                $content = "Hi " . $notifiable->name . ",\n\n" .
                          $this->liker->name . " has liked your profile!\n\n" .
                          "Check out their profile and maybe like them back for a potential match!\n\n" .
                          "View their profile: " . url('/profile/view/' . $this->liker->id) . "\n\n" .
                          "Don't miss out on potential connections!\n\n" .
                          "Best regards,\nZawajAfrica Team";
            } else {
                // For free users - anonymous message with upgrade prompt
                $subject = '💕 Someone likes you on ZawajAfrica!';
                $content = "Hi " . $notifiable->name . ",\n\n" .
                          "Someone has liked your profile!\n\n" .
                          "Upgrade to a paid plan to see who liked you and unlock more features!\n\n" .
                          "Upgrade now: " . url('/subscription') . "\n\n" .
                          "Don't miss out on potential connections!\n\n" .
                          "Best regards,\nZawajAfrica Team";
            }

            // Send simple text email
            \Mail::raw($content, function ($message) use ($notifiable, $subject) {
                $message->to($notifiable->email)
                        ->subject($subject);
            });

            \Log::info('Like email sent successfully (delayed)', [
                'liker_id' => $this->liker->id,
                'receiver_id' => $notifiable->id,
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send like email', [
                'liker_id' => $this->liker->id,
                'receiver_id' => $notifiable->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if we can reveal the liker's name based on receiver's tier
     */
    private function canRevealLiker(): bool
    {
        // Free users see anonymous messages, paid users see actual names
        return in_array($this->receiverTier, [
            UserTierService::TIER_BASIC,
            UserTierService::TIER_GOLD, 
            UserTierService::TIER_PLATINUM
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // Configure Zoho Mail before sending
        $zohoMailService = app(ZohoMailService::class);
        $zohoMailService->configureMailer();

        $canReveal = $this->canRevealLiker();
        
        if ($canReveal) {
            // For paid users - show actual liker's name
            $subject = '💕 ' . $this->liker->name . ' likes you on ZawajAfrica!';
            $mainMessage = $this->liker->name . ' has liked your profile!';
            $encouragement = 'Check out their profile and maybe like them back for a potential match!';
        } else {
            // For free users - anonymous message with upgrade prompt
            $subject = '💕 Someone likes you on ZawajAfrica!';
            $mainMessage = 'Someone has liked your profile!';
            $encouragement = 'Upgrade to a paid plan to see who liked you and unlock more features!';
        }

        $message = $this->createMatchEmail($subject, $notifiable->name)
            ->line($mainMessage)
            ->line($encouragement);

        if ($canReveal) {
            $message->action('View Their Profile', url('/matches/profile/' . $this->liker->id));
        } else {
            $message->action('Upgrade Now', url('/subscription'));
        }

        return $this->addIslamicBlessing(
            $this->addProfessionalFooter(
                $message->line('Don\'t miss out on potential connections!')
            )
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $canReveal = $this->canRevealLiker();
        
        if ($canReveal) {
            // For paid users - show actual liker's name and photo
            return [
                'title' => 'New Like Received! 💕',
                'message' => $this->liker->name . ' liked your profile!',
                'icon' => 'heart',
                'color' => 'pink',
                'action_text' => 'View Profile',
                'action_url' => route('profile.view', ['id' => $this->liker->id]),
                'liker_id' => $this->liker->id,
                'liker_name' => $this->liker->name,
                'liker_photo' => $this->liker->profile_photo ? asset('storage/' . $this->liker->profile_photo) : null,
                'can_reveal' => true,
                'receiver_tier' => $this->receiverTier
            ];
        } else {
            // For free users - anonymous message with upgrade prompt
            return [
                'title' => 'Someone likes you! 💕',
                'message' => 'Someone liked your profile! Upgrade to see who it was.',
                'icon' => 'heart',
                'color' => 'yellow',
                'action_text' => 'Upgrade Plan',
                'action_url' => route('subscription.index'),
                'liker_id' => null,
                'liker_name' => null,
                'liker_photo' => null,
                'can_reveal' => false,
                'receiver_tier' => $this->receiverTier
            ];
        }
    }
}
