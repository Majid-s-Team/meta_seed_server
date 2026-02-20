<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production reliability: log stream failures, dropped streams, retry detection.
 * Does not implement the detection engine; structure only for future use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livestream_stream_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('livestream_id')->index();
            $table->string('event_type', 64); // feed_detected | feed_stopped | failure | retry_detected | manual_override
            $table->text('message')->nullable();
            $table->json('metadata')->nullable(); // bitrate, uptime, etc.
            $table->timestamps();

            $table->foreign('livestream_id')->references('id')->on('livestreams')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestream_stream_logs');
    }
};
