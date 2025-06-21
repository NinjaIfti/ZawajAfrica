<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TherapistBooking;

class TherapistBookingPending extends Notification // Remove ShouldQueue for immediate processing
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
            ->subject('⏳ Therapy Session Booking Pending Payment')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('Your therapy session booking is pending payment confirmation.')
            ->line('**Therapist:** ' . $this->booking->therapist->name)
            ->line('**Session Date:** ' . $this->booking->appointment_datetime->format('l, F j, Y'))
            ->line('**Session Time:** ' . $this->booking->appointment_datetime->format('g:i A'))
            ->line('**Session Type:** ' . ucfirst($this->booking->session_type))
            ->line('**Amount:** ₦' . number_format($this->booking->amount, 2))
            ->action('Complete Payment', url('/therapists/bookings'))
            ->line('Please complete your payment to confirm this booking.')
            ->line('Note: Unpaid bookings will be automatically cancelled after 24 hours.')
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
            'type' => 'booking_pending',
            'title' => 'Booking Pending Payment',
            'message' => 'Your session with ' . $this->booking->therapist->name . ' is pending payment. Complete payment to confirm.',
            'booking_id' => $this->booking->id,
            'therapist_name' => $this->booking->therapist->name,
            'therapist_photo' => $this->booking->therapist->photo_url,
            'appointment_date' => $this->booking->appointment_datetime->format('M j, Y'),
            'appointment_time' => $this->booking->appointment_datetime->format('g:i A'),
            'session_type' => ucfirst($this->booking->session_type),
            'amount' => '₦' . number_format($this->booking->amount, 2),
            'action_url' => '/therapists/bookings',
            'action_text' => 'Complete Payment',
            'icon' => 'clock',
            'color' => 'orange',
            'created_at' => now()->toISOString(),
        ];
    }
}
