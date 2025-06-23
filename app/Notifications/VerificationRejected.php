<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Traits\ZohoMailTemplate;

class VerificationRejected extends Notification
{
    use Queueable, ZohoMailTemplate;

    private string $rejectionReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $rejectionReason)
    {
        $this->rejectionReason = $rejectionReason;
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
        $message = $this->createSystemEmail('❌ Verification Documents Need Review', $notifiable->name)
            ->line('We have reviewed your verification documents, and unfortunately, we need you to resubmit them.')
            ->line('**Reason for review request:**')
            ->line($this->rejectionReason)
            ->line('')
            ->line('**Next Steps:**')
            ->line('• Review the feedback above carefully')
            ->line('• Prepare new, clear photos of your documents')
            ->line('• Ensure all information is clearly visible')
            ->line('• Resubmit through your profile verification section')
            ->action('Resubmit Verification', url('/verification/intro'))
            ->line('**Document Requirements:**')
            ->line('• Clear, well-lit photos')
            ->line('• All text must be readable')
            ->line('• No blurry or cropped images')
            ->line('• Documents should be valid and unexpired')
            ->line('')
            ->line('We appreciate your patience and look forward to welcoming you as a verified member of ZawajAfrica!');

        return $this->addProfessionalFooter($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'verification_rejected',
            'message' => 'Your verification documents need to be resubmitted.',
            'reason' => $this->rejectionReason,
            'action_url' => url('/verification/intro'),
            'action_text' => 'Resubmit Documents'
        ];
    }
} 