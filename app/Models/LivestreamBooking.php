<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivestreamBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'livestream_id',
        'booked_at',
        'access_expires_at',
        'amount_paid',
        'access_tier',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function livestream()
    {
        return $this->belongsTo(Livestream::class);
    }

    /** Whether access has expired (join not allowed after this). */
    public function isAccessExpired(): bool
    {
        return $this->access_expires_at && $this->access_expires_at->isPast();
    }
}
