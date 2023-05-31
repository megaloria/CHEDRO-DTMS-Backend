<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DocumentApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $document;
    private $log;
    private $by;

    public function __construct($document, $log, $by=null)
    {
        $this->afterCommit();

        $this->document = $document;
        $this->log = $log;
        $this->by = $by;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = config('constants.APP_CLIENT_URL').'/documents/view/'.$this->document['id'];

        return (new MailMessage)
                    ->subject('DTMS Notification - '.$this->document['tracking_no'])
                    ->greeting('Hello!')
                    ->line('The document '.$this->document['tracking_no'].' has been approved by '.$this->by['name'])
                    ->lineIf(isset($this->log['comment']) && $this->log['comment'], isset($this->log['comment']) && $this->log['comment'] ? $this->log['comment'] : '')
                    ->action('View Document', $url)
                    ->line('Thank you for using our application!');
    }

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
            'by' => $this->by
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'unread_notifications_count' => $notifiable->unread_notifications_count,
            'document' => $this->document,
            'log' => $this->log,
            'by' => $this->by
        ]);
    }
}
