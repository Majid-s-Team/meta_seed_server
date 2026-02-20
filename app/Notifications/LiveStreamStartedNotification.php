<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LiveStreamStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $livestreamTitle,
        public int $livestreamId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'live_started',
            'title' => 'Live stream started',
            'message' => "{$this->livestreamTitle} is now live. Tune in!",
            'livestream_id' => $this->livestreamId,
            'livestream_title' => $this->livestreamTitle,
        ];
    }
}
