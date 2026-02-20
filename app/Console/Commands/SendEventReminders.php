<?php

namespace App\Console\Commands;

use App\Models\EventBooking;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'notifications:event-reminders';
    protected $description = 'Send event reminder notifications for events happening tomorrow';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();
        $bookings = EventBooking::with(['user', 'event'])
            ->whereHas('event', fn ($q) => $q->whereDate('date', $tomorrow))
            ->get();

        $sent = 0;
        foreach ($bookings as $booking) {
            if ($booking->user && $booking->event) {
                $booking->user->notify(new EventReminderNotification(
                    $booking->event->title,
                    $booking->event->date,
                    $booking->event->time ?? ''
                ));
                $sent++;
            }
        }

        $this->info("Sent {$sent} event reminder(s).");
        return self::SUCCESS;
    }
}
