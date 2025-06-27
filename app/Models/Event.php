<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    protected $fillable = [
        'title', 'description', 'date', 'time', 'total_seats', 'available_seats',
        'coins', 'category_id', 'is_online', 'status'
    ];

    public function category() {
        return $this->belongsTo(EventCategory::class);
    }

    public function bookings() {
        return $this->hasMany(EventBooking::class);
    }
}