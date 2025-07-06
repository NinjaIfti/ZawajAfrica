<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Services\ZohoMailService;
use App\Traits\ZohoMailTemplate;
use Carbon\Carbon;

class BirthdayReminder extends Notification implements ShouldQueue
{
    use Queueable, ZohoMailTemplate;

    private User $partner;
    private Carbon $birthday;
    private int $daysUntilBirthday;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $partner, Carbon $birthday, int $daysUntilBirthday)
    {
        $this->partner = $partner;
        $this->birthday = $birthday;
        $this->daysUntilBirthday = $daysUntilBirthday;
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
        $daysText = $this->daysUntilBirthday === 1 ? 'tomorrow' : "in {$this->daysUntilBirthday} days";
        $subject = "🎂 {$this->partner->name}'s birthday is {$daysText}!";
        
        // Get personalized gift suggestions based on partner's gender
        $giftSuggestions = $this->getGiftSuggestions();
        
        $content = "Hey {$notifiable->name}!\n\n" .
                  "Just a heads-up – your chosen partner {$this->partner->name}'s birthday is coming up on {$this->birthday->format('F j, Y')}!\n\n" .
                  "Looking for the perfect gift? Based on their profile, we think they'd love something special from our gift store 🎁:\n" .
                  "{$giftSuggestions}\n" .
                  "👉 Shop now at https://www.thejannahkabeer.com.ng\n\n" .
                  "Make their special day even more memorable!\n\n" .
                  "Best regards,\nZawajAfrica Team";

        return (new MailMessage)
            ->subject($subject)
            ->line("Hey {$notifiable->name}!")
            ->line("Just a heads-up – your chosen partner {$this->partner->name}'s birthday is coming up on {$this->birthday->format('F j, Y')}!")
            ->line("Looking for the perfect gift? Based on their profile, we think they'd love something special from our gift store 🎁:")
            ->line($giftSuggestions)
            ->action('Shop for Gifts', 'https://www.thejannahkabeer.com.ng')
            ->line('Make their special day even more memorable!')
            ->line('Best regards, ZawajAfrica Team');
    }



    /**
     * Get personalized gift suggestions based on partner's gender
     */
    private function getGiftSuggestions(): string
    {
        $suggestions = [];
        
        if ($this->partner->gender === 'female') {
            $suggestions = [
                "• Beautiful hijabs and modest fashion items",
                "• Elegant jewelry and accessories",
                "• Skincare and beauty products",
                "• Home decor and lifestyle items",
                "• Books and educational materials"
            ];
        } elseif ($this->partner->gender === 'male') {
            $suggestions = [
                "• Traditional Islamic clothing",
                "• Quality watches and accessories",
                "• Grooming and personal care items",
                "• Tech gadgets and accessories",
                "• Books and educational materials"
            ];
        } else {
            // Gender-neutral suggestions
            $suggestions = [
                "• Islamic books and literature",
                "• Prayer mats and religious items",
                "• Quality clothing and accessories",
                "• Home and lifestyle products",
                "• Educational and personal development items"
            ];
        }
        
        return implode("\n", $suggestions);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $daysText = $this->daysUntilBirthday === 1 ? 'tomorrow' : "in {$this->daysUntilBirthday} days";
        
        return [
            'title' => "🎂 {$this->partner->name}'s Birthday {$daysText}!",
            'message' => "Your partner {$this->partner->name}'s birthday is coming up on {$this->birthday->format('F j, Y')}. Find the perfect gift at TheJannahKabeer Gift Store!",
            'icon' => 'gift',
            'color' => 'pink',
            'action_text' => 'Shop for Gifts',
            'action_url' => 'https://www.thejannahkabeer.com.ng',
            'partner_id' => $this->partner->id,
            'partner_name' => $this->partner->name,
            'birthday_date' => $this->birthday->format('Y-m-d'),
            'days_until_birthday' => $this->daysUntilBirthday,
            'gift_store_url' => 'https://www.thejannahkabeer.com.ng'
        ];
    }
} 