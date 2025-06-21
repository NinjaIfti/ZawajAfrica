<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewLikeReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private User $liker;
    private bool $canReveal;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $liker, bool $canReveal = false)
    {
        $this->liker = $liker;
        $this->canReveal = $canReveal;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('💕 Someone likes you on ZawajAfrica!')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line($this->canReveal 
                ? $this->liker->name . ' has liked your profile!'
                : 'Someone has liked your profile!')
            ->line('Check out who\'s interested in you and maybe like them back!')
            ->action('View Messages', url('/messages'))
            ->line('May Allah bless your search for a righteous partner!')
            ->salutation('Best wishes, The ZawajAfrica Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->canReveal) {
            return [
                'title' => 'New Like Received! 💕',
                'message' => $this->liker->name . ' liked your profile!',
                'icon' => 'heart',
                'color' => 'pink',
                'action_text' => null, // No clickable action
                'action_url' => null,  // Not clickable
                'liker_id' => $this->liker->id,
                'liker_name' => $this->liker->name,
                'liker_photo' => $this->liker->profile_photo ? asset('storage/' . $this->liker->profile_photo) : null
            ];
        } else {
            return [
                'title' => 'Someone likes you! 💕',
                'message' => 'Someone liked your profile! Upgrade to see who it was.',
                'icon' => 'heart',
                'color' => 'yellow',
                'action_text' => 'Upgrade Plan',
                'action_url' => route('subscription.index'),
                'liker_id' => null,
                'liker_name' => null,
                'liker_photo' => null
            ];
        }
    }
}
