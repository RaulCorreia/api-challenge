<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiverTransaction extends Notification
{
    use Queueable;

    private $userFrom;
    private $userTo;
    private $amount;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->userFrom = $transaction->sender->name;
        $this->userTo = $transaction->receiver->name;
        $this->amount = number_format($transaction->amount, 2, ',', '.');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Transferência recebida')
                    ->greeting("Olá  {$this->userFrom}")
                    ->line("Você recebeu uma transferência de {$this->userFrom}")
                    ->line("no valor de {$this->amount} R$.")
                    ->salutation("Atenciosamente Api Challenge");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
