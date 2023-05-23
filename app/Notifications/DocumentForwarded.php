<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentForwarded extends Notification
{
    use Queueable;

    private $document;
    private $log;
    private $from;
    private $to;

    /**
     * Create a new notification instance.
     */
    public function __construct($document, $log, $from=null, $to=null)
    {
        $this->afterCommit();

        $this->document = $document;
        $this->log = $log;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document' => $this->document->toArray(),
            'log' => $this->log->toArray(),
            'from' => $this->from,
            'to' => $this->to
        ];
    }
}