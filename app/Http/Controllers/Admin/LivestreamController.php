<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use App\Models\LivestreamStreamLog;
use App\Notifications\LiveStreamStartedNotification;
use Illuminate\Http\Request;

class LivestreamController extends Controller
{
    public function index(Request $request)
    {
        $query = Livestream::with('creator:id,name')->orderBy('scheduled_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $livestreams = $query->paginate(15);
        return view('admin.livestreams.index', compact('livestreams'));
    }

    public function create()
    {
        return view('admin.livestreams.create');
    }

    /**
     * Store new livestream. Auto-generates RTMP URL and stream key when broadcast_type is rtmp
     * and rtmp_url/rtmp_stream_key are not provided. Format: rtmp://push.agora.io/live/{channel}, stream_key = {channel}.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string|max:500',
            'scheduled_at' => 'required|date',
            'agora_channel' => 'required|string|max:64',
            'broadcast_type' => 'nullable|in:agora_rtc,rtmp',
            'rtmp_url' => 'nullable|string|max:500',
            'rtmp_stream_key' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'overlay_enabled' => 'nullable|boolean',
            'scoreboard_overlay_url' => 'nullable|string|max:500',
            'recording_enabled' => 'nullable|boolean',
        ]);
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'scheduled';
        $validated['broadcast_type'] = $validated['broadcast_type'] ?? Livestream::BROADCAST_TYPE_AGORA_RTC;
        $validated['overlay_enabled'] = $request->boolean('overlay_enabled');
        $validated['recording_enabled'] = $request->boolean('recording_enabled');

        // Auto-generate RTMP credentials when using RTMP broadcast and not provided
        if (($validated['broadcast_type'] ?? '') === Livestream::BROADCAST_TYPE_RTMP) {
            $channel = $validated['agora_channel'];
            if (empty($validated['rtmp_url'])) {
                $validated['rtmp_url'] = Livestream::defaultRtmpUrlForChannel($channel);
            }
            if (empty($validated['rtmp_stream_key'])) {
                $validated['rtmp_stream_key'] = $channel;
            }
        }

        Livestream::create($validated);
        return redirect()->route('admin.livestreams.index')->with('success', 'Livestream scheduled.');
    }

    public function edit(Livestream $livestream)
    {
        if ($livestream->status !== 'scheduled') {
            return redirect()->route('admin.livestreams.index')->with('error', 'Only scheduled streams can be edited.');
        }
        return view('admin.livestreams.edit', compact('livestream'));
    }

    public function update(Request $request, Livestream $livestream)
    {
        if ($livestream->status !== 'scheduled') {
            return redirect()->route('admin.livestreams.index')->with('error', 'Only scheduled streams can be edited.');
        }
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string|max:500',
            'scheduled_at' => 'sometimes|date',
            'agora_channel' => 'sometimes|string|max:64',
            'broadcast_type' => 'nullable|in:agora_rtc,rtmp',
            'rtmp_url' => 'nullable|string|max:500',
            'rtmp_stream_key' => 'nullable|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'max_participants' => 'sometimes|integer|min:1',
            'overlay_enabled' => 'nullable|boolean',
            'scoreboard_overlay_url' => 'nullable|string|max:500',
            'recording_enabled' => 'nullable|boolean',
        ]);
        if (isset($validated['broadcast_type']) && $validated['broadcast_type'] === Livestream::BROADCAST_TYPE_RTMP) {
            $channel = $validated['agora_channel'] ?? $livestream->agora_channel;
            if (empty($validated['rtmp_url'])) {
                $validated['rtmp_url'] = Livestream::defaultRtmpUrlForChannel($channel);
            }
            if (empty($validated['rtmp_stream_key'])) {
                $validated['rtmp_stream_key'] = $channel;
            }
        }
        $validated['overlay_enabled'] = $request->boolean('overlay_enabled');
        $validated['recording_enabled'] = $request->boolean('recording_enabled');
        $livestream->update($validated);
        return redirect()->route('admin.livestreams.index')->with('success', 'Livestream updated.');
    }

    public function destroy(Livestream $livestream)
    {
        if ($livestream->status === 'live') {
            return redirect()->route('admin.livestreams.index')->with('error', 'End the stream first.');
        }
        $livestream->delete();
        return redirect()->route('admin.livestreams.index')->with('success', 'Livestream deleted.');
    }

    /**
     * Broadcast panel: RTMP connection details, OBS setup instructions, connection status, health, bitrate/uptime placeholders.
     */
    public function broadcast(Livestream $livestream)
    {
        return view('admin.livestreams.broadcast', compact('livestream'));
    }

    /**
     * Manual Go Live: sets status to live, stream_started_at, stream_health_status to waiting_for_broadcaster.
     * Broadcast workflow can later set live_receiving_feed when RTMP feed is detected (unless manual override).
     */
    public function goLive(Livestream $livestream)
    {
        if ($livestream->status !== 'scheduled') {
            return back()->with('error', 'Only scheduled streams can go live.');
        }
        $livestream->update([
            'status' => 'live',
            'stream_started_at' => now(),
            'stream_health_status' => Livestream::STREAM_HEALTH_STATUS_WAITING,
        ]);
        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_MANUAL_OVERRIDE,
            'message' => 'Admin set stream to live (manual go live)',
        ]);
        foreach ($livestream->bookings()->with('user')->get() as $booking) {
            if ($booking->user) {
                $booking->user->notify(new LiveStreamStartedNotification($livestream->title, $livestream->id));
            }
        }
        return back()->with('success', 'Stream is now LIVE.');
    }

    /**
     * Manual End Stream: sets status ended, computes revenue_earned and stream_duration_seconds for analytics.
     */
    public function endStream(Livestream $livestream)
    {
        if ($livestream->status !== 'live') {
            return back()->with('error', 'Stream is not live.');
        }
        $started = $livestream->stream_started_at ?? $livestream->updated_at;
        $durationSeconds = $started ? (int) now()->diffInSeconds($started) : 0;
        $revenue = (float) $livestream->bookings()->sum('amount_paid');

        $endedAt = now();
        $livestream->update([
            'status' => 'ended',
            'ended_at' => $endedAt,
            'current_viewer_count' => 0,
            'stream_health_status' => Livestream::STREAM_HEALTH_STATUS_OFFLINE,
            'stream_duration_seconds' => $durationSeconds,
            'revenue_earned' => $revenue,
        ]);
        // Optional: set access expiration on bookings (e.g. 24h after stream end for replay window)
        $expiresAt = $endedAt->copy()->addHours(config('services.livestream.access_expiry_hours', 24));
        $livestream->bookings()->whereNull('access_expires_at')->update(['access_expires_at' => $expiresAt]);
        LivestreamStreamLog::create([
            'livestream_id' => $livestream->id,
            'event_type' => LivestreamStreamLog::EVENT_MANUAL_OVERRIDE,
            'message' => 'Admin ended stream',
            'metadata' => ['stream_duration_seconds' => $durationSeconds, 'revenue_earned' => $revenue],
        ]);
        return back()->with('success', 'Stream ended.');
    }
}
