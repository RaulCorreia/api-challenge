<?php

namespace App\Notifications;

use App\Models\Transaction;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionFail extends Notification
{
    use Queueable;

    private $userFrom;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $userFrom)
    {
        $this->userFrom = $userFrom->name;
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
                    ->subject('Transferência não realizada')
                    ->greeting("Olá  {$this->userFrom}")
                    ->line("Ocorreu um problema com a sua transferência")
                    ->line("contate o suporte para mais informações.")
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
