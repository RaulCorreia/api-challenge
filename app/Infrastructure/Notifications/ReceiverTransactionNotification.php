<?php

namespace App\Infrastructure\Notifications;

use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiverTransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly TransactionEntity $transaction
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('You received a transfer')
            ->greeting("Hello, {$notifiable->name}!")
            ->line('A transfer has been deposited into your account.')
            ->line("Amount: R$ {$this->transaction->amount->format()}")
            ->line("Date: {$this->transaction->transactionDate->format('d/m/Y H:i:s')}")
            ->salutation('UTransfer Team');
    }
}

