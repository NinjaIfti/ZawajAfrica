<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\ZohoMailTemplate;

class VerificationApproved extends Notification
{
    use Queueable, ZohoMailTemplate;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $message = $this->createSystemEmail('🎉 Verification Approved - Welcome to ZawajAfrica!', $notifiable->name)
            ->line('🎉 **Congratulations!** Your account verification has been approved.')
            ->line('You are now a verified member of the ZawajAfrica community!')
            ->line('')
            ->line('**🔓 Your Account Now Has Full Access To:**')
            ->line('✅ Browse all verified profiles')
            ->line('✅ Send and receive unlimited messages')
            ->line('✅ Access advanced matching features')
            ->line('✅ Book therapy sessions with licensed counselors')
            ->line('✅ Priority customer support')
            ->line('✅ Verified badge on your profile')
            ->line('')
            ->action('Start Exploring Matches', url('/dashboard'))
            ->line('**🌟 Welcome to ZawajAfrica!**')
            ->line('We are excited to support you on your journey to find a righteous and compatible partner.')
            ->line('')
            ->line('May Allah bless your search and grant you a marriage filled with love, understanding, and faith.');

        return $this->addIslamicBlessing($this->addProfessionalFooter($message));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'verification_approved',
            'title' => 'Verification Approved!',
            'message' => 'Congratulations! Your account verification has been approved. Welcome to ZawajAfrica!',
            'action_url' => '/dashboard',
            'action_text' => 'Start Exploring Matches',
            'icon' => 'shield-check',
            'color' => 'green',
            'created_at' => now()->toISOString(),
        ];
    }
}
