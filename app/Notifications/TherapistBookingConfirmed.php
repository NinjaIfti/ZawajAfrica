<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TherapistBooking;
use App\Traits\ZohoMailTemplate;
use App\Services\ZohoMailService;

class TherapistBookingConfirmed extends Notification // Remove ShouldQueue for immediate processing
{
    use Queueable, ZohoMailTemplate;

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
     * Send the notification via mail using therapist sender address.
     */
    public function toMail($notifiable)
    {
        $message = $this->createBookingEmail('✅ Therapy Session Confirmed!', $notifiable->name)
            ->line('Great news! Your therapy session has been confirmed.')
            ->line('We are excited to support you on your journey towards emotional well-being.');

        // Add appointment details using the trait method
        $this->formatAppointmentDetails($message, $this->booking);

        // Add meeting link if available
        $this->addMeetingLink($message, $this->booking);

        $message = $message
            ->action('View Booking Details', url('/my-bookings'))
            ->line('**📋 Before Your Session:**')
            ->line('• Ensure you have a stable internet connection')
            ->line('• Find a quiet, private space for your session')
            ->line('• Have any questions or topics you\'d like to discuss ready')
            ->line('')
            ->line('We wish you a beneficial and healing session!');

        return $this->addProfessionalFooter($message);
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
