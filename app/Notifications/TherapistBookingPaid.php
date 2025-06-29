<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TherapistBooking;
use App\Services\ZohoMailService;

class TherapistBookingPaid extends Notification // Remove ShouldQueue for immediate processing
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
            ->subject('💳 Payment Confirmed - Therapy Session Booked!')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('Great news! Your payment has been successfully processed.')
            ->line('**Therapist:** ' . $this->booking->therapist->name)
            ->line('**Session Date:** ' . $this->booking->appointment_datetime->format('l, F j, Y'))
            ->line('**Session Time:** ' . $this->booking->appointment_datetime->format('g:i A'))
            ->line('**Session Type:** ' . ucfirst($this->booking->session_type))
            ->line('**Amount Paid:** ₦' . number_format($this->booking->amount, 2))
            ->line('**Payment Reference:** ' . $this->booking->payment_reference)
            ->action('View Booking Details', url('/therapists/bookings'))
            ->line('You will receive a session reminder 24 hours before your appointment.')
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
        return [
            'type' => 'booking_paid',
            'title' => 'Payment Confirmed!',
            'message' => 'Payment successful for session with ' . $this->booking->therapist->name . ' on ' . $this->booking->appointment_datetime->format('M j, Y \a\t g:i A'),
            'booking_id' => $this->booking->id,
            'therapist_name' => $this->booking->therapist->name,
            'therapist_photo' => $this->booking->therapist->photo_url,
            'appointment_date' => $this->booking->appointment_datetime->format('M j, Y'),
            'appointment_time' => $this->booking->appointment_datetime->format('g:i A'),
            'session_type' => ucfirst($this->booking->session_type),
            'amount' => '₦' . number_format($this->booking->amount, 2),
            'payment_reference' => $this->booking->payment_reference,
            'action_url' => '/therapists/bookings',
            'action_text' => 'View Details',
            'icon' => 'credit-card',
            'color' => 'green',
            'created_at' => now()->toISOString(),
        ];
    }
}
