<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DocumentForwardedTo extends Notification
{
    use Queueable;

    private $document;
    private $log;
    private $to;

    /**
     * Create a new notification instance.
     */
    public function __construct($document, $log, $to=null)
    {
        $this->afterCommit();

        $this->document = $document;
        $this->log = $log;
        $this->to = $to;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
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
    public function toDatabase(object $notifiable): array
    {
        return [
            'document' => $this->document,
            'log' => $this->log,
            'to' => $this->to
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'unread_notifications_count' => $notifiable->unread_notifications_count,
            'document' => $this->document,
            'log' => $this->log,
            'from' => $this->from,
            'to' => $this->to
        ]);
    }
}
