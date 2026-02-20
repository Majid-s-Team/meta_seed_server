<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livestreams', function (Blueprint $table) {
            $table->string('rtmp_url', 500)->nullable()->after('agora_channel');
            $table->string('rtmp_stream_key', 255)->nullable()->after('rtmp_url');
            $table->unsignedInteger('current_viewer_count')->default(0)->after('max_participants');
            $table->string('stream_health', 50)->nullable()->after('current_viewer_count'); // ok, degraded, offline
            $table->timestamp('ended_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('livestreams', function (Blueprint $table) {
            $table->dropColumn(['rtmp_url', 'rtmp_stream_key', 'current_viewer_count', 'stream_health', 'ended_at']);
        });
    }
};
