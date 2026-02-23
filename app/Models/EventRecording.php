<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRecording extends Model
{
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'video_path',
        'video_url',
        'thumbnail_url',
        'recorded_at',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'is_visible' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getPlayableUrlAttribute(): ?string
    {
        if ($this->video_url) {
            return $this->video_url;
        }
        if ($this->video_path) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->video_path);
        }
        return null;
    }
}
