<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Services\ZohoMailService;
use App\Traits\ZohoMailTemplate;

class BirthdayWishes extends Notification implements ShouldQueue
{
    use Queueable, ZohoMailTemplate;

    private User $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        return now()->addMinutes(5);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $age = $this->user->age ?? 'another year';
        $subject = "🎉 Happy Birthday, {$this->user->name}! 🎂";
        
        // Get personalized birthday message
        $birthdayMessage = $this->getBirthdayMessage();
        
        return (new MailMessage)
            ->subject($subject)
            ->line("🎉 Happy Birthday, {$this->user->name}! 🎂")
            ->line("Wishing you a blessed and joyful {$age}th year ahead!")
            ->line($birthdayMessage)
            ->line("As a valued premium member of ZawajAfrica, may Allah bless you with happiness, good health, and meaningful connections.")
            ->line("🎁 To celebrate your special day, enjoy exclusive access to our gift store:")
            ->action('Shop Birthday Gifts', 'https://www.thejannahkabeer.com.ng')
            ->line("May this new year of life bring you closer to finding your perfect match!")
            ->line('With warm birthday wishes,')
            ->salutation('The ZawajAfrica Team 💝');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'birthday_wishes',
            'title' => 'Happy Birthday! 🎉',
            'message' => "Wishing you a blessed and joyful birthday! May this new year bring you happiness and meaningful connections.",
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'action_url' => 'https://www.thejannahkabeer.com.ng'
        ];
    }

    /**
     * Get personalized birthday message based on user's profile
     */
    private function getBirthdayMessage(): string
    {
        $messages = [
            "May Allah shower you with His countless blessings on this special day and always.",
            "On your birthday, we pray that Allah grants you success, happiness, and a righteous spouse.",
            "Barakallahu feeki/feeka! May this birthday mark the beginning of your best year yet.",
            "As you celebrate another year of life, may Allah bless you with peace, prosperity, and true love.",
            "Happy Birthday! May Allah guide you to all that is good and protect you from all harm."
        ];

        return $messages[array_rand($messages)];
    }
}
