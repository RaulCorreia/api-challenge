<?php

namespace App\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $senderName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your transfer could not be completed')
            ->greeting("Hello, {$this->senderName}!")
            ->line('Unfortunately, your transfer failed to process.')
            ->line('No amount was deducted from your account.')
            ->line('If you believe this is an error, please contact our support team.')
            ->salutation('UTransfer Team');
    }
}

