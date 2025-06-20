<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TherapistBooking;

class TherapistBookingCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    private TherapistBooking $booking;
    private string $cancellationReason;
    private bool $refundIssued;

    /**
     * Create a new notification instance.
     */
    public function __construct(TherapistBooking $booking, string $cancellationReason = '', bool $refundIssued = false)
    {
        $this->booking = $booking;
        $this->cancellationReason = $cancellationReason;
        $this->refundIssued = $refundIssued;
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
        $mail = (new MailMessage)
            ->subject('❌ Therapy Session Cancelled')
            ->greeting('Salam Alaikum ' . $notifiable->name . '!')
            ->line('We regret to inform you that your therapy session has been cancelled.')
            ->line('**Therapist:** ' . $this->booking->therapist->name)
            ->line('**Original Date:** ' . $this->booking->appointment_datetime->format('l, F j, Y \a\t g:i A'))
            ->line('**Session Type:** ' . ucfirst($this->booking->session_type));

        if ($this->cancellationReason) {
            $mail->line('**Reason:** ' . $this->cancellationReason);
        }

        if ($this->refundIssued) {
            $mail->line('**Refund:** A full refund of ₦' . number_format($this->booking->amount, 2) . ' has been initiated and will be processed within 3-5 business days.');
        }

        $mail->action('Book Another Session', url('/therapists'))
            ->line('We apologize for any inconvenience caused.')
            ->salutation('Best regards, The ZawajAfrica Team');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'booking_cancelled',
            'title' => 'Session Cancelled',
            'message' => 'Your session with ' . $this->booking->therapist->name . ' on ' . $this->booking->appointment_datetime->format('M j, Y') . ' has been cancelled' . ($this->refundIssued ? ' (Refund issued)' : ''),
            'booking_id' => $this->booking->id,
            'therapist_name' => $this->booking->therapist->name,
            'therapist_photo' => $this->booking->therapist->photo_url,
            'appointment_date' => $this->booking->appointment_datetime->format('M j, Y'),
            'appointment_time' => $this->booking->appointment_datetime->format('g:i A'),
            'session_type' => ucfirst($this->booking->session_type),
            'cancellation_reason' => $this->cancellationReason,
            'refund_issued' => $this->refundIssued,
            'refund_amount' => $this->refundIssued ? '₦' . number_format($this->booking->amount, 2) : null,
            'action_url' => '/therapists',
            'action_text' => 'Book Another Session',
            'icon' => 'x-circle',
            'color' => 'red',
            'created_at' => now()->toISOString(),
        ];
    }
}
