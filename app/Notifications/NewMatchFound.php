<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Services\ZohoMailService;
use App\Traits\ZohoMailTemplate;

class NewMatchFound extends Notification // Remove ShouldQueue for immediate processing
{
    use Queueable, ZohoMailTemplate;

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
        // Log the email sending attempt
        \Log::info('NewMatchFound toMail called', [
            'recipient_id' => $notifiable->id,
            'recipient_name' => $notifiable->name,
            'recipient_email' => $notifiable->email,
            'match_with' => $this->match->name
        ]);

        // Configure Zoho Mail before sending
        $zohoMailService = app(ZohoMailService::class);
        $zohoMailService->configureMailer();

        $subject = '🌟 It\'s a Match! You and ' . $this->match->name . ' liked each other!';

        $message = $this->createMatchEmail($subject, $notifiable->name)
            ->line('🎉 **Congratulations!** You have a new match!')
            ->line('You and **' . $this->match->name . '** both liked each other!')
            ->line('')
            ->line('This means you can now start messaging each other and get to know one another better.')
            ->action('Start Messaging', url('/messages'))
            ->line('**Next Steps:**')
            ->line('• Send a thoughtful first message')
            ->line('• Be genuine and respectful in your conversations')
            ->line('• Take your time to get to know each other')
            ->line('')
            ->line('We\'re excited to see where this connection leads!');

        $finalMessage = $this->addIslamicBlessing(
            $this->addProfessionalFooter($message)
        );

        \Log::info('NewMatchFound email prepared successfully', [
            'recipient_email' => $notifiable->email,
            'subject' => $subject
        ]);

        return $finalMessage;
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
