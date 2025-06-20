<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TherapistBooking;

class TherapistBookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    private TherapistBooking $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(TherapistBooking $booking)
    {
        $this->booking = $booking;
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
            ->subject('✅ Therapy Session Confirmed!')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('Great news! Your therapy session has been confirmed.')
            ->line('**Therapist:** ' . $this->booking->therapist->name)
            ->line('**Date & Time:** ' . $this->booking->appointment_datetime->format('l, F j, Y \a\t g:i A'))
            ->line('**Session Type:** ' . ucfirst($this->booking->session_type))
            ->when($this->booking->meeting_link, function ($mail) {
                return $mail->line('**Meeting Link:** ' . $this->booking->meeting_link);
            })
            ->action('View Booking Details', url('/my-bookings'))
            ->line('We wish you a beneficial session!')
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
            'type' => 'booking_confirmed',
            'title' => 'Therapy Session Confirmed',
            'message' => 'Your session with ' . $this->booking->therapist->name . ' has been confirmed',
            'booking_id' => $this->booking->id,
            'therapist_name' => $this->booking->therapist->name,
            'therapist_photo' => $this->booking->therapist->photo_url,
            'appointment_date' => $this->booking->appointment_datetime->format('M j, Y'),
            'appointment_time' => $this->booking->appointment_datetime->format('g:i A'),
            'session_type' => ucfirst($this->booking->session_type),
            'action_url' => '/my-bookings',
            'action_text' => 'View Details',
            'icon' => 'calendar-check',
            'color' => 'green',
            'created_at' => now()->toISOString(),
        ];
    }
}
