<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livestream extends Model
{
    use HasFactory;

    public const BROADCAST_TYPE_AGORA_RTC = 'agora_rtc';
    public const BROADCAST_TYPE_RTMP = 'rtmp';

    public const STREAM_HEALTH_STATUS_WAITING = 'waiting_for_broadcaster';
    public const STREAM_HEALTH_STATUS_LIVE = 'live_receiving_feed';
    public const STREAM_HEALTH_STATUS_OFFLINE = 'stream_offline';

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'scheduled_at',
        'status',
        'agora_channel',
        'broadcast_type',
        'rtmp_url',
        'rtmp_stream_key',
        'price',
        'max_participants',
        'current_viewer_count',
        'stream_health',
        'stream_health_status',
        'peak_viewer_count',
        'total_viewer_count',
        'average_watch_time_seconds',
        'stream_duration_seconds',
        'revenue_earned',
        'scoreboard_overlay_url',
        'overlay_enabled',
        'recording_enabled',
        'recording_url',
        'highlights_ready',
        'ended_at',
        'stream_started_at',
        'last_rtmp_activity_at',
        'status_manual_override',
        'stream_bitrate_kbps',
        'stream_uptime_seconds',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'ended_at' => 'datetime',
        'stream_started_at' => 'datetime',
        'last_rtmp_activity_at' => 'datetime',
        'price' => 'decimal:2',
        'revenue_earned' => 'decimal:2',
        'overlay_enabled' => 'boolean',
        'recording_enabled' => 'boolean',
        'highlights_ready' => 'boolean',
        'status_manual_override' => 'boolean',
    ];

    /**
     * Admin/user who created the livestream.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Bookings for this livestream.
     */
    public function bookings()
    {
        return $this->hasMany(LivestreamBooking::class);
    }

    /**
     * Stream logs for reliability (failures, feed detected/stopped, retries).
     */
    public function streamLogs()
    {
        return $this->hasMany(LivestreamStreamLog::class);
    }

    /** Whether stream is free (price zero). */
    public function isFree(): bool
    {
        return (float) $this->price === 0.0;
    }

    /**
     * Default RTMP server URL for OBS (Agora RTLS Ingress).
     * Used when broadcast_type is rtmp and URL not set manually.
     * Stream key is generated via Agora RTLS API (see AgoraRtlsService), not derived from channel.
     */
    public static function defaultRtmpUrlForChannel(string $channel): string
    {
        return \App\Services\AgoraRtlsService::defaultRtmpServerUrl();
    }
}
