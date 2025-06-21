<?php

namespace App\Traits;

use Illuminate\Notifications\Messages\MailMessage;

trait ZohoMailTemplate
{
    /**
     * Create a professionally formatted ZawajAfrica email message
     */
    protected function createZohoMailMessage(): MailMessage
    {
        return (new MailMessage)
            ->from(config('services.zoho_mail.from_address'), config('services.zoho_mail.from_name'))
            ->replyTo(config('services.zoho_mail.from_address'))
            ->metadata('X-ZawajAfrica-Source', 'notification')
            ->metadata('X-ZawajAfrica-Environment', app()->environment());
    }

    /**
     * Apply professional ZawajAfrica email styling
     */
    protected function styleZohoEmail(MailMessage $message): MailMessage
    {
        return $message
            ->theme('zawaj-africa') // Custom theme (can be created later)
            ->metadata('X-Mailer', 'ZawajAfrica-Platform')
            ->priority(3); // Normal priority
    }

    /**
     * Create a booking-related email template
     */
    protected function createBookingEmail(string $subject, string $userName): MailMessage
    {
        return $this->createZohoMailMessage()
            ->subject($subject)
            ->greeting('Salam Alaikum ' . $userName . '!')
            ->salutation('Best regards,<br>The ZawajAfrica Support Team<br><small>Your trusted partner in halal relationships</small>');
    }

    /**
     * Create a match-related email template
     */
    protected function createMatchEmail(string $subject, string $userName): MailMessage
    {
        return $this->createZohoMailMessage()
            ->subject($subject)
            ->greeting('Salam Alaikum ' . $userName . '!')
            ->salutation('Best wishes for your journey,<br>The ZawajAfrica Team<br><small>Connecting African Muslims worldwide</small>');
    }

    /**
     * Create a system notification email template
     */
    protected function createSystemEmail(string $subject, string $userName): MailMessage
    {
        return $this->createZohoMailMessage()
            ->subject($subject)
            ->greeting('Salam Alaikum ' . $userName . '!')
            ->salutation('Thank you,<br>The ZawajAfrica Team<br><small>support@zawajafrica.online</small>');
    }

    /**
     * Add professional footer with contact information
     */
    protected function addProfessionalFooter(MailMessage $message): MailMessage
    {
        return $message
            ->line('---')
            ->line('**Need help?** Contact our support team at support@zawajafrica.online')
            ->line('Visit us: [ZawajAfrica.online](https://zawajafrica.online)')
            ->line('<small>This email was sent from a monitored email address. Please do not reply directly to this email.</small>');
    }

    /**
     * Add Islamic greeting and blessing
     */
    protected function addIslamicBlessing(MailMessage $message): MailMessage
    {
        return $message
            ->line('May Allah bless your journey towards finding a righteous partner.');
    }

    /**
     * Format appointment details consistently
     */
    protected function formatAppointmentDetails(MailMessage $message, $booking): MailMessage
    {
        return $message
            ->line('**📅 Appointment Details:**')
            ->line('• **Therapist:** ' . $booking->therapist->name)
            ->line('• **Date:** ' . $booking->appointment_datetime->format('l, F j, Y'))
            ->line('• **Time:** ' . $booking->appointment_datetime->format('g:i A T'))
            ->line('• **Duration:** 1 hour')
            ->line('• **Session Type:** ' . ucfirst($booking->session_type ?? 'video_call'));
    }

    /**
     * Add meeting link if available
     */
    protected function addMeetingLink(MailMessage $message, $booking): MailMessage
    {
        if (!empty($booking->meeting_link)) {
            return $message
                ->line('**🔗 Meeting Link:**')
                ->action('Join Session', $booking->meeting_link);
        }
        
        return $message;
    }

    /**
     * Create urgent/priority email template
     */
    protected function createUrgentEmail(string $subject, string $userName): MailMessage
    {
        return $this->createZohoMailMessage()
            ->subject('🚨 URGENT: ' . $subject)
            ->priority(1) // High priority
            ->greeting('Salam Alaikum ' . $userName . '!')
            ->line('**This is an urgent notification that requires your immediate attention.**')
            ->salutation('Urgent regards,<br>The ZawajAfrica Team');
    }
} 