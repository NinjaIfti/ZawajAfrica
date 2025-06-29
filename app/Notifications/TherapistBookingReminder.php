<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TherapistBooking;
use App\Services\ZohoMailService;

class TherapistBookingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    private TherapistBooking $booking;
    private string $reminderType; // '24h', '1h', '15m'

    /**
     * Create a new notification instance.
     */
    public function __construct(TherapistBooking $booking, string $reminderType = '24h')
    {
        $this->booking = $booking;
        $this->reminderType = $reminderType;
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
        $timeText = $this->getTimeText();
        $urgencyText = $this->getUrgencyText();

        return (new MailMessage)
            ->subject($urgencyText . ' Session Reminder - ' . $this->booking->therapist->name)
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line($urgencyText . ' you have a therapy session scheduled.')
            ->line('**Therapist:** ' . $this->booking->therapist->name)
            ->line('**Date & Time:** ' . $this->booking->appointment_datetime->format('l, F j, Y \a\t g:i A'))
            ->line('**Session Type:** ' . ucfirst($this->booking->session_type))
            ->when($this->booking->meeting_link, function ($mail) {
                return $mail->line('**Meeting Link:** ' . $this->booking->meeting_link);
            })
            ->when($this->reminderType === '15m', function ($mail) {
                return $mail->action('Join Session Now', $this->booking->meeting_link ?: url('/therapists/bookings'));
            }, function ($mail) {
                return $mail->action('View Session Details', url('/therapists/bookings'));
            })
            ->line('Please be ready a few minutes before your scheduled time.')
            ->salutation('Best regards, The ZawajAfrica Team');
    }

    /**
     * Send the mail using ZohoMailService with therapist sender type
     */
    public function send($notifiable, $notification)
    {
        if (in_array('mail', $this->via($notifiable))) {
            $zohoMail = app(ZohoMailService::class);
            $mailable = $this->toMail($notifiable);
            $zohoMail->sendTherapistEmail($mailable, $notifiable);
        }
        
        // Continue with other channels
        parent::send($notifiable, $notification);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $timeText = $this->getTimeText();
        $urgencyText = $this->getUrgencyText();

        return [
            'type' => 'booking_reminder',
            'title' => $urgencyText . ' Session Reminder',
            'message' => $urgencyText . ' you have a session with ' . $this->booking->therapist->name . ' at ' . $this->booking->appointment_datetime->format('g:i A'),
            'booking_id' => $this->booking->id,
            'therapist_name' => $this->booking->therapist->name,
            'therapist_photo' => $this->booking->therapist->photo_url,
            'appointment_date' => $this->booking->appointment_datetime->format('M j, Y'),
            'appointment_time' => $this->booking->appointment_datetime->format('g:i A'),
            'session_type' => ucfirst($this->booking->session_type),
            'meeting_link' => $this->booking->meeting_link,
            'reminder_type' => $this->reminderType,
            'time_until' => $timeText,
            'action_url' => $this->reminderType === '15m' && $this->booking->meeting_link 
                ? $this->booking->meeting_link 
                : '/therapists/bookings',
            'action_text' => $this->reminderType === '15m' ? 'Join Now' : 'View Details',
            'icon' => 'calendar-clock',
            'color' => $this->reminderType === '15m' ? 'red' : ($this->reminderType === '1h' ? 'orange' : 'blue'),
            'created_at' => now()->toISOString(),
        ];
    }

    private function getTimeText(): string
    {
        return match($this->reminderType) {
            '24h' => 'in 24 hours',
            '1h' => 'in 1 hour',
            '15m' => 'in 15 minutes',
            default => 'soon'
        };
    }

    private function getUrgencyText(): string
    {
        return match($this->reminderType) {
            '24h' => '📅 Tomorrow',
            '1h' => '⏰ Soon',
            '15m' => '🚨 Starting Soon',
            default => 'Reminder'
        };
    }
}
