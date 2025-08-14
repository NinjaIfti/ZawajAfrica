<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\ZohoMailTemplate;
use Carbon\Carbon;

class SubscriptionExpiring extends Notification implements ShouldQueue
{
    use Queueable, ZohoMailTemplate;

    private string $planName;
    private Carbon $expiresAt;
    private int $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $planName, Carbon $expiresAt, int $daysLeft)
    {
        $this->planName = $planName;
        $this->expiresAt = $expiresAt;
        $this->daysLeft = $daysLeft;
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
        $daysText = $this->daysLeft === 1 ? 'tomorrow' : "in {$this->daysLeft} days";
        $subject = "⚠️ Your {$this->planName} subscription expires {$daysText}";
        
        $urgencyMessage = $this->getUrgencyMessage();
        
        return (new MailMessage)
            ->subject($subject)
            ->line("Hi {$notifiable->name},")
            ->line("⚠️ Your {$this->planName} subscription will expire on {$this->expiresAt->format('F j, Y')} ({$daysText}).")
            ->line($urgencyMessage)
            ->line("Don't let your premium features expire! Renew now to continue enjoying:")
            ->line("✅ Unlimited profile views and connections")
            ->line("✅ Advanced matching algorithms")
            ->line("✅ Priority customer support")
            ->line("✅ Access to exclusive events and features")
            ->action('Renew Subscription', url('/subscription'))
            ->line("Need help? Our support team is here to assist you.")
            ->line('Best regards,')
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
            'type' => 'subscription_expiring',
            'title' => 'Subscription Expiring Soon ⚠️',
            'message' => "Your {$this->planName} subscription expires in {$this->daysLeft} days. Renew now to keep your premium features!",
            'plan' => $this->planName,
            'expires_at' => $this->expiresAt->toISOString(),
            'days_left' => $this->daysLeft,
            'action_url' => url('/subscription')
        ];
    }

    /**
     * Get urgency message based on days left
     */
    private function getUrgencyMessage(): string
    {
        if ($this->daysLeft === 1) {
            return "🚨 URGENT: Your subscription expires tomorrow! Don't lose access to your premium features.";
        } elseif ($this->daysLeft <= 3) {
            return "⏰ Only {$this->daysLeft} days left! Renew now to avoid any interruption in your premium experience.";
        } else {
            return "📅 You have {$this->daysLeft} days to renew your subscription. Secure your premium access today!";
        }
    }
}
