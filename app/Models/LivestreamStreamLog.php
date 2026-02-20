<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestreamStreamLog extends Model
{
    protected $table = 'livestream_stream_logs';

    protected $fillable = [
        'livestream_id',
        'event_type',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function livestream()
    {
        return $this->belongsTo(Livestream::class);
    }

    public const EVENT_FEED_DETECTED = 'feed_detected';
    public const EVENT_FEED_STOPPED = 'feed_stopped';
    public const EVENT_BROADCAST_FAILURE = 'broadcast_failure';
    public const EVENT_FAILURE = 'failure';
    public const EVENT_RETRY_DETECTED = 'retry_detected';
    public const EVENT_MANUAL_OVERRIDE = 'manual_override';
}
