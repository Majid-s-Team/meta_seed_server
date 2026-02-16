<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livestream extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'scheduled_at',
        'status',
        'agora_channel',
        'price',
        'max_participants',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'price' => 'decimal:2',
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
}
