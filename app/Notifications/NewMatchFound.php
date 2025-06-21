<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewMatchFound extends Notification implements ShouldQueue
{
    use Queueable;

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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🌟 New Match Found on ZawajAfrica!')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('Great news! We found a new potential match for you.')
            ->line('**' . $this->match->name . '** has joined ZawajAfrica and matches your preferences.')
            ->action('View Profile', url('/matches/profile/' . $this->match->id))
            ->line('Don\'t miss this opportunity to connect with someone special!')
            ->salutation('Best wishes for your journey, The ZawajAfrica Team');
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
