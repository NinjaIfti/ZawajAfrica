<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Message;

class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    private User $sender;
    private Message $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $sender, Message $message)
    {
        $this->sender = $sender;
        $this->message = $message;
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
        // Configure Zoho Mail before sending
        $zohoMailService = app(\App\Services\ZohoMailService::class);
        $zohoMailService->configureMailer();

        $messagePreview = strlen($this->message->content) > 100 
            ? substr($this->message->content, 0, 100) . '...' 
            : $this->message->content;

        return (new MailMessage)
            ->subject('💬 New Message from ' . $this->sender->name)
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('You have received a new message from **' . $this->sender->name . '**.')
            ->line('"' . $messagePreview . '"')
            ->action('Read Message', url('/messages/' . $this->sender->id))
            ->line('Stay connected and build meaningful relationships!')
            ->salutation('Best regards, The ZawajAfrica Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'new_message',
            'title' => 'New Message',
            'message' => $this->sender->name . ' sent you a message',
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'sender_photo' => $this->sender->profile_photo ? asset('storage/' . $this->sender->profile_photo) : null,
            'message_preview' => strlen($this->message->content) > 50 
                ? substr($this->message->content, 0, 50) . '...' 
                : $this->message->content,
            'action_url' => '/messages/' . $this->sender->id,
            'action_text' => 'Read Message',
            'icon' => 'chat',
            'color' => 'blue',
            'created_at' => now()->toISOString(),
        ];
    }
}
