<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ProfileViewed extends Notification implements ShouldQueue
{
    use Queueable;

    private User $viewer;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $viewer)
    {
        $this->viewer = $viewer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database']; // Only database, no email for profile views
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'profile_viewed',
            'title' => 'Profile Viewed',
            'message' => $this->viewer->name . ' viewed your profile',
            'viewer_id' => $this->viewer->id,
            'viewer_name' => $this->viewer->name,
            'viewer_photo' => $this->viewer->profile_photo ? asset('storage/' . $this->viewer->profile_photo) : null,
            'action_url' => '/matches/profile/' . $this->viewer->id,
            'action_text' => 'View Their Profile',
            'icon' => 'eye',
            'color' => 'indigo',
            'created_at' => now()->toISOString(),
        ];
    }
}
