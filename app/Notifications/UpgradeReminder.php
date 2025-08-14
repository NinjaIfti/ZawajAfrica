<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\ZohoMailTemplate;
use App\Models\User;

class UpgradeReminder extends Notification implements ShouldQueue
{
    use Queueable, ZohoMailTemplate;

    private string $reminderType;
    private array $benefits;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $reminderType = 'general')
    {
        $this->reminderType = $reminderType;
        $this->benefits = $this->getBenefits();
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
        $subject = $this->getSubject();
        $message = $this->getMessage($notifiable);
        
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line("Assalamu Alaikum {$notifiable->name},")
            ->line($message);

        // Add benefits
        foreach ($this->benefits as $benefit) {
            $mailMessage->line($benefit);
        }

        $mailMessage
            ->action('Upgrade to Premium', url('/subscription'))
            ->line($this->getCallToAction())
            ->line('May Allah bless your journey to find your perfect match,')
            ->salutation('The ZawajAfrica Team 💝');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'upgrade_reminder',
            'reminder_type' => $this->reminderType,
            'title' => 'Upgrade to Premium 🌟',
            'message' => 'Unlock unlimited features and find your perfect match faster with our premium plans!',
            'action_url' => url('/subscription'),
            'benefits' => $this->benefits
        ];
    }

    /**
     * Get subject based on reminder type
     */
    private function getSubject(): string
    {
        $subjects = [
            'general' => '🌟 Unlock Your Perfect Match with Premium!',
            'activity' => '💝 You\'re So Close to Finding Love - Upgrade Now!',
            'limited_views' => '👀 You\'ve Reached Your Daily Limit - Go Premium!',
            'special_offer' => '🎉 Special Offer: 20% Off Premium Subscription!',
            'success_story' => '💑 Join 1000+ Success Stories - Upgrade Today!'
        ];

        return $subjects[$this->reminderType] ?? $subjects['general'];
    }

    /**
     * Get message based on reminder type
     */
    private function getMessage($notifiable): string
    {
        $messages = [
            'general' => "You've been exploring ZawajAfrica and we love having you in our community! Ready to take your journey to the next level?",
            'activity' => "We've noticed you're actively looking for your perfect match. Premium features can help you connect faster and more effectively!",
            'limited_views' => "You've reached your daily profile view limit. Don't let this stop your search for love!",
            'special_offer' => "For a limited time, we're offering 20% off all premium subscriptions just for you!",
            'success_story' => "Over 1000 couples have found love through ZawajAfrica's premium features. You could be next!"
        ];

        return $messages[$this->reminderType] ?? $messages['general'];
    }

    /**
     * Get premium benefits
     */
    private function getBenefits(): array
    {
        return [
            '🔥 Unlimited profile views and connections',
            '💎 Advanced matching algorithms',
            '⚡ Priority in search results',
            '💬 Unlimited messaging with all members',
            '🎯 See who viewed your profile',
            '🛡️ Enhanced privacy controls',
            '📞 Priority customer support',
            '🎁 Access to exclusive events and features'
        ];
    }

    /**
     * Get call to action based on reminder type
     */
    private function getCallToAction(): string
    {
        $ctas = [
            'general' => 'Join thousands of premium members who found their perfect match!',
            'activity' => 'Don\'t let your soulmate slip away - upgrade now!',
            'limited_views' => 'Remove all limits and find your match today!',
            'special_offer' => 'This offer expires soon - claim your discount now!',
            'success_story' => 'Your success story starts with premium membership!'
        ];

        return $ctas[$this->reminderType] ?? $ctas['general'];
    }
}
