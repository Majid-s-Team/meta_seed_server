<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Broadcast workflow & production readiness.
 * - RTMP: broadcast_type, stream_health_status (waiting_for_broadcaster | live_receiving_feed | stream_offline).
 * - Analytics: peak_viewer_count, total_viewer_count, average_watch_time_seconds, stream_duration_seconds, revenue_earned.
 * - Overlay: scoreboard_overlay_url, overlay_enabled.
 * - Recording: recording_enabled, recording_url, highlights_ready.
 * - Automation/reliability: stream_started_at, last_rtmp_activity_at, status_manual_override, stream_bitrate_kbps, stream_uptime_seconds.
 * Backward compatible: existing rtmp_url / rtmp_stream_key unchanged; new fields nullable or have defaults.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livestreams', function (Blueprint $table) {
            $table->string('broadcast_type', 32)->default('agora_rtc')->after('agora_channel'); // agora_rtc | rtmp
            $table->string('stream_health_status', 64)->nullable()->after('stream_health'); // waiting_for_broadcaster | live_receiving_feed | stream_offline

            $table->unsignedInteger('peak_viewer_count')->default(0)->after('current_viewer_count');
            $table->unsignedInteger('total_viewer_count')->default(0)->after('peak_viewer_count');
            $table->unsignedInteger('average_watch_time_seconds')->default(0)->after('total_viewer_count');
            $table->unsignedInteger('stream_duration_seconds')->default(0)->after('average_watch_time_seconds');
            $table->decimal('revenue_earned', 12, 2)->default(0)->after('stream_duration_seconds');

            $table->string('scoreboard_overlay_url', 500)->nullable()->after('rtmp_stream_key');
            $table->boolean('overlay_enabled')->default(false)->after('scoreboard_overlay_url');

            $table->boolean('recording_enabled')->default(false)->after('overlay_enabled');
            $table->string('recording_url', 500)->nullable()->after('recording_enabled');
            $table->boolean('highlights_ready')->default(false)->after('recording_url');

            $table->timestamp('stream_started_at')->nullable()->after('ended_at');
            $table->timestamp('last_rtmp_activity_at')->nullable()->after('stream_started_at');
            $table->boolean('status_manual_override')->default(false)->after('last_rtmp_activity_at');
            $table->unsignedInteger('stream_bitrate_kbps')->nullable()->after('status_manual_override');
            $table->unsignedInteger('stream_uptime_seconds')->nullable()->after('stream_bitrate_kbps');
        });
    }

    public function down(): void
    {
        Schema::table('livestreams', function (Blueprint $table) {
            $table->dropColumn([
                'broadcast_type', 'stream_health_status',
                'peak_viewer_count', 'total_viewer_count', 'average_watch_time_seconds', 'stream_duration_seconds', 'revenue_earned',
                'scoreboard_overlay_url', 'overlay_enabled',
                'recording_enabled', 'recording_url', 'highlights_ready',
                'stream_started_at', 'last_rtmp_activity_at', 'status_manual_override', 'stream_bitrate_kbps', 'stream_uptime_seconds',
            ]);
        });
    }
};
