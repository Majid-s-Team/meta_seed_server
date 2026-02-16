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
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function livestream()
    {
        return $this->belongsTo(Livestream::class);
    }
}
