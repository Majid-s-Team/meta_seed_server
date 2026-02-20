<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
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
            'type' => 'event_reminder',
            'title' => 'Event tomorrow',
            'message' => "Reminder: {$this->eventTitle} is tomorrow ({$this->eventDate} at {$this->eventTime}).",
            'event_title' => $this->eventTitle,
            'event_date' => $this->eventDate,
            'event_time' => $this->eventTime,
        ];
    }
}
