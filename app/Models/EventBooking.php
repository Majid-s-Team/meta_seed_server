<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBooking extends Model {
    protected $fillable = ['user_id', 'event_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
      public function event()
    {
        return $this->belongsTo(Event::class);
    }
}