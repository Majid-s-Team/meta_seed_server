<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Transaction extends Model {
    protected $fillable = ['user_id', 'type', 'amount', 'commission_amount', 'description', 'reference_type', 'reference_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
