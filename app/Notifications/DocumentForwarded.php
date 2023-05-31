<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

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
        return ['database','broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = config('constants.APP_CLIENT_URL').'/documents/view/'.$this->document['id'];

        $line = 'The document '.$this->document['tracking_no'].' has been forwarded';
        if (($this->from && $this->from['id'] === $notifiable->id) || ($notifiable->role->level <= 2 && $this->to && $this->to['id'] !== $notifiable->id)) {
            $line = 'The document '.$this->document['tracking_no'].' has been forwarded to '.$this->to['name'];
        } else if ($this->to && $this->to['id'] === $notifiable->id) {
            $line = 'The document '.$this->document['tracking_no'].' has been forwarded from '.$this->from['name'];
        }

        return (new MailMessage)
                    ->subject('DTMS Notification - '.$this->document['tracking_no'])
                    ->greeting('Hello!')
                    ->line($line)
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
            'from' => $this->from,
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
