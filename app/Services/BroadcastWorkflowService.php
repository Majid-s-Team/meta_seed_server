<?php

namespace App\Services;

use App\Models\Livestream;
use App\Models\LivestreamStreamLog;
use Illuminate\Support\Facades\Cache;

/**
 * Broadcast workflow: RTMP status automation, logging, reliability.
 * - When RTMP feed is detected → set status = live, stream_health_status = live_receiving_feed (unless manual override).
 * - When feed stops → set status = ended, stream_health_status = stream_offline (unless manual override).
 * - Duplicate detection and cooldown prevent status flapping; manual override is always respected.
 */
class BroadcastWorkflowService
{
    private const RECENT_ACTIVITY_SECONDS = 30;
    private const FEED_STOPPED_COOLDOWN_KEY = 'broadcast_feed_stopped_cooldown_%d';
    private const FEED_STOPPED_COOLDOWN_SECONDS = 15;

    public function reportRtmpFeedDetected(Livestream $livestream, array $metadata = []): void
    {
        if ($livestream->status_manual_override) {
            LivestreamStreamLog::create([
                'livestream_id' => $livestream->id,
                'event_type' => LivestreamStreamLog::EVENT_FEED_DETECTED,
                'message' => 'RTMP feed detected but status is under manual override; no auto transition.',
                'metadata' => $metadata,
            ]);
            return;
        }

        $cooldownKey = sprintf(self::FEED_STOPPED_COOLDOWN_KEY, $livestream->id);
        if (Cache::has($cooldownKey)) {
            LivestreamStreamLog::create([
                'livestream_id' => $livestream->id,
                'event_type' => LivestreamStreamLog::EVENT_RETRY_DETECTED,
                'message' => 'RTMP feed detected during cooldown after stop; ignored to prevent flapping.',
                'metadata' => $metadata,
            ]);
            return;
        }

        $livestream->refresh();
        $now = now();
        $recentActivity = $livestream->last_rtmp_activity_at
            && $livestream->last_rtmp_activity_at->diffInSeconds($now, false) <= self::RECENT_ACTIVITY_SECONDS;

        if ($livestream->status === 'live' && $livestream->stream_health_status === Livestream::STREAM_HEALTH_STATUS_LIVE && $recentActivity) {
            $livestream->update(['last_rtmp_activity_at' => $now]);
            return;
        }

        $wasLive = $livestream->status === 'live';
        $livestream->update([
            'status' => 'live',
            'stream_health_status' => Livestream::STREAM_HEALTH_STATUS_LIVE,
            'stream_started_at' => $livestream->stream_started_at ?? $now,
            'last_rtmp_activity_at' => $now,
        ]);

        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_FEED_DETECTED,
            'message' => $wasLive ? 'RTMP feed re-detected' : 'RTMP feed detected; status set to live',
            'metadata' => $metadata,
        ]);
    }

    public function reportRtmpFeedStopped(Livestream $livestream, array $metadata = []): void
    {
        if ($livestream->status_manual_override) {
            LivestreamStreamLog::create([
                'livestream_id' => $livestream->id,
                'event_type' => LivestreamStreamLog::EVENT_FEED_STOPPED,
                'message' => 'RTMP feed stopped but status is under manual override; no auto end.',
                'metadata' => $metadata,
            ]);
            return;
        }

        if ($livestream->status === 'ended') {
            return;
        }

        $livestream->update([
            'status' => 'ended',
            'ended_at' => $livestream->ended_at ?? now(),
            'stream_health_status' => Livestream::STREAM_HEALTH_STATUS_OFFLINE,
            'current_viewer_count' => 0,
        ]);

        Cache::put(sprintf(self::FEED_STOPPED_COOLDOWN_KEY, $livestream->id), true, self::FEED_STOPPED_COOLDOWN_SECONDS);

        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_FEED_STOPPED,
            'message' => 'RTMP feed stopped; status set to ended',
            'metadata' => $metadata,
        ]);
    }

    public function logFailure(Livestream $livestream, string $message, array $metadata = []): void
    {
        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_FAILURE,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }

    public function logBroadcastFailure(Livestream $livestream, string $message, array $metadata = []): void
    {
        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_BROADCAST_FAILURE,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }

    public function logRetryDetected(Livestream $livestream, string $message, array $metadata = []): void
    {
        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_RETRY_DETECTED,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }
}
