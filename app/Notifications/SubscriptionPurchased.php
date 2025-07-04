<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\ZohoMailTemplate;

class SubscriptionPurchased extends Notification
{
    use Queueable, ZohoMailTemplate;

    private string $planName;
    private float $amount;
    private string $paymentReference;
    private \DateTime $expiresAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $planName, float $amount, string $paymentReference, \DateTime $expiresAt)
    {
        $this->planName = $planName;
        $this->amount = $amount;
        $this->paymentReference = $paymentReference;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database']; // Handle email separately via Zoho
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // Configure Zoho Mail before sending
        $zohoMailService = app(\App\Services\ZohoMailService::class);
        $zohoMailService->configureMailer();

        $subject = '🎉 Subscription Activated - Welcome to ' . $this->planName . ' Plan!';

        $message = $this->createSystemEmail($subject, $notifiable->name)
            ->line('🎉 **Congratulations!** Your subscription payment has been successfully processed.')
            ->line('Your **' . $this->planName . ' Plan** is now active!')
            ->line('')
            ->line('**💳 Payment Details:**')
            ->line('• **Plan:** ' . $this->planName)
            ->line('• **Amount Paid:** ₦' . number_format($this->amount, 2))
            ->line('• **Payment Reference:** ' . $this->paymentReference)
            ->line('• **Valid Until:** ' . $this->expiresAt->format('l, F j, Y \a\t g:i A'))
            ->line('')
            ->line('**🔓 Your ' . $this->planName . ' Benefits:**');

        // Add plan-specific benefits
        if ($this->planName === 'Basic') {
            $message->line('✅ Unlimited profile browsing')
                ->line('✅ Send messages to matched users')
                ->line('✅ Access to basic search filters')
                ->line('✅ Customer support');
        } elseif ($this->planName === 'Gold') {
            $message->line('✅ All Basic plan features')
                ->line('✅ See who liked your profile')
                ->line('✅ Advanced search filters')
                ->line('✅ Priority customer support')
                ->line('✅ Read receipts for messages');
        } elseif ($this->planName === 'Platinum') {
            $message->line('✅ All Gold plan features')
                ->line('✅ Message anyone without matching first')
                ->line('✅ Unlimited likes and super likes')
                ->line('✅ Boost your profile visibility')
                ->line('✅ Premium customer support')
                ->line('✅ Advanced privacy controls');
        }

        $message = $message
            ->line('')
            ->action('Start Exploring', url('/dashboard'))
            ->line('**🌟 Start Your Journey!**')
            ->line('Your subscription is now active and you can enjoy all the premium features.')
            ->line('');

        return $this->addIslamicBlessing(
            $this->addProfessionalFooter($message)
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'subscription_purchased',
            'title' => 'Subscription Activated!',
            'message' => 'Your ' . $this->planName . ' plan is now active. Enjoy premium features!',
            'plan_name' => $this->planName,
            'amount' => '₦' . number_format($this->amount, 2),
            'payment_reference' => $this->paymentReference,
            'expires_at' => $this->expiresAt->format('M j, Y'),
            'action_url' => '/dashboard',
            'action_text' => 'Start Exploring',
            'icon' => 'star',
            'color' => 'gold',
            'created_at' => now()->toISOString(),
        ];
    }
} 