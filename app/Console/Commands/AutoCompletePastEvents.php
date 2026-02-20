<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class AutoCompletePastEvents extends Command
{
    protected $signature = 'events:auto-complete';
    protected $description = 'Set event status to completed when event date has passed';

    public function handle(): int
    {
        $today = now()->toDateString();
        $updated = Event::whereIn('status', ['active', 'inactive'])
            ->where('date', '<', $today)
            ->update(['status' => 'completed']);

        if ($updated > 0) {
            $this->info("Marked {$updated} event(s) as completed.");
        }

        return self::SUCCESS;
    }
}
