<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('livestream_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('livestream_bookings_user_id_foreign');
            $table->unsignedBigInteger('livestream_id')->index('livestream_bookings_livestream_id_foreign');
            $table->dateTime('booked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestream_bookings');
    }
};
