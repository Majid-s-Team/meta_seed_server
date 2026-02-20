<?php

namespace App\Console\Commands;

use App\Models\Livestream;
use App\Services\BroadcastWorkflowService;
use Illuminate\Console\Command;

/**
 * Optional fallback: mark streams as offline when no RTMP activity for too long.
 * Run every 30s (or every minute) via scheduler. Does not override manual control.
 *
 * Logic: livestreams with status=live and stream_health_status=live_receiving_feed
 * and last_rtmp_activity_at older than threshold → call reportRtmpFeedStopped
 * (which respects status_manual_override).
 */
class LivestreamHealthCheckCommand extends Command
{
    protected $signature = 'livestream:health-check
                            {--threshold=120 : Seconds without RTMP activity before marking offline}';

    protected $description = 'Check livestream health and mark offline if RTMP feed inactive (no manual override)';

    public function handle(BroadcastWorkflowService $workflow): int
    {
        $threshold = (int) $this->option('threshold');
        $cutoff = now()->subSeconds($threshold);

        $streams = Livestream::where('status', 'live')
            ->where('stream_health_status', Livestream::STREAM_HEALTH_STATUS_LIVE)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_rtmp_activity_at')->orWhere('last_rtmp_activity_at', '<', $cutoff);
            })
            ->where('status_manual_override', false)
            ->get();

        foreach ($streams as $livestream) {
            $workflow->reportRtmpFeedStopped($livestream, [
                'source' => 'livestream:health-check',
                'threshold_seconds' => $threshold,
                'last_rtmp_activity_at' => $livestream->last_rtmp_activity_at?->toIso8601String(),
            ]);
            $this->info("Marked livestream {$livestream->id} ({$livestream->agora_channel}) offline (no activity).");
        }

        return self::SUCCESS;
    }
}
