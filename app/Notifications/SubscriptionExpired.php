<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\ZohoMailTemplate;
use Carbon\Carbon;

class SubscriptionExpired extends Notification implements ShouldQueue
{
    use Queueable, ZohoMailTemplate;

    private string $planName;
    private Carbon $expiredAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $planName, Carbon $expiredAt)
    {
        $this->planName = $planName;
        $this->expiredAt = $expiredAt;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Determine the time at which the email should be sent.
     */
    public function retryUntil()
    {
        return now()->addMinutes(10);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $subject = "💔 Your {$this->planName} subscription has expired";
        
        return (new MailMessage)
            ->subject($subject)
            ->line("Hi {$notifiable->name},")
            ->line("💔 We're sorry to inform you that your {$this->planName} subscription expired on {$this->expiredAt->format('F j, Y')}.")
            ->line("Your account has been moved to our free tier, which means:")
            ->line("❌ Limited profile views per day")
            ->line("❌ Restricted messaging capabilities")
            ->line("❌ No access to advanced matching features")
            ->line("But don't worry! You can reactivate your premium features anytime:")
            ->line("🎯 Get back unlimited access")
            ->line("💝 Continue your journey to find your perfect match")
            ->line("🌟 Enjoy all premium benefits again")
            ->action('Reactivate Premium', url('/subscription'))
            ->line("Special offer: Renew within 7 days and get 10% off your next subscription!")
            ->line('We hope to welcome you back to premium soon,')
            ->salutation('The ZawajAfrica Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'subscription_expired',
            'title' => 'Subscription Expired 💔',
            'message' => "Your {$this->planName} subscription has expired. Reactivate now to restore your premium features!",
            'plan' => $this->planName,
            'expired_at' => $this->expiredAt->toISOString(),
            'action_url' => url('/subscription'),
            'special_offer' => 'Renew within 7 days and get 10% off!'
        ];
    }
}
