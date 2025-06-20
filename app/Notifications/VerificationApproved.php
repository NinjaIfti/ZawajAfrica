<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationApproved extends Notification implements ShouldQueue
{
    use Queueable;

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
        return (new MailMessage)
            ->subject('🎉 Verification Approved - Welcome to ZawajAfrica!')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('Congratulations! Your account verification has been approved.')
            ->line('You now have full access to all ZawajAfrica features:')
            ->line('• Browse verified profiles')
            ->line('• Send unlimited messages')
            ->line('• Access premium matching features')
            ->line('• Book therapy sessions')
            ->action('Start Exploring', url('/dashboard'))
            ->line('Welcome to the ZawajAfrica community!')
            ->salutation('Best wishes on your journey, The ZawajAfrica Team');
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
            'message' => 'Your account has been verified. Welcome to ZawajAfrica!',
            'action_url' => '/dashboard',
            'action_text' => 'Start Exploring',
            'icon' => 'shield-check',
            'color' => 'green',
            'created_at' => now()->toISOString(),
        ];
    }
}
