<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketPurchaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $eventTitle,
        public string $eventDate,
        public string $eventTime
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_purchase',
            'title' => 'Ticket confirmed',
            'message' => "You've booked a ticket for {$this->eventTitle} on {$this->eventDate} at {$this->eventTime}.",
            'event_title' => $this->eventTitle,
            'event_date' => $this->eventDate,
            'event_time' => $this->eventTime,
        ];
    }
}
