<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use App\Models\LivestreamStreamLog;
use App\Notifications\LiveStreamStartedNotification;
use App\Services\AgoraRtlsService;
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

        // Auto-generate RTMP credentials via Agora RTLS API when using RTMP and not provided
        $streamKeyWarning = null;
        if (($validated['broadcast_type'] ?? '') === Livestream::BROADCAST_TYPE_RTMP) {
            $channel = $validated['agora_channel'];
            if (empty($validated['rtmp_url'])) {
                $validated['rtmp_url'] = Livestream::defaultRtmpUrlForChannel($channel);
            }
            if (empty($validated['rtmp_stream_key'])) {
                try {
                    $rtls = app(AgoraRtlsService::class);
                    $validated['rtmp_stream_key'] = $rtls->createStreamKey($channel, '1', 0);
                } catch (\Throwable $e) {
                    // Still create the livestream; key can be generated on broadcast page when env is fixed
                    $streamKeyWarning = 'Stream key could not be generated: ' . $e->getMessage() . '. Set AGORA_APP_CERTIFICATE in .env or generate on the broadcast page.';
                }
            }
        }

        Livestream::create($validated);
        $redirect = redirect()->route('admin.livestreams.index')->with('success', 'Livestream scheduled.');
        if ($streamKeyWarning) {
            $redirect->with('warning', $streamKeyWarning);
        }
        return $redirect;
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
        $streamKeyWarning = null;
        if (isset($validated['broadcast_type']) && $validated['broadcast_type'] === Livestream::BROADCAST_TYPE_RTMP) {
            $channel = $validated['agora_channel'] ?? $livestream->agora_channel;
            if (empty($validated['rtmp_url'])) {
                $validated['rtmp_url'] = Livestream::defaultRtmpUrlForChannel($channel);
            }
            if (empty($validated['rtmp_stream_key'])) {
                try {
                    $rtls = app(AgoraRtlsService::class);
                    $validated['rtmp_stream_key'] = $rtls->createStreamKey($channel, (string) $livestream->id, 0);
                } catch (\Throwable $e) {
                    $streamKeyWarning = 'Stream key could not be generated. Set AGORA_APP_CERTIFICATE or generate on broadcast page.';
                    // Keep existing key if any; otherwise leave empty
                    $validated['rtmp_stream_key'] = $livestream->rtmp_stream_key;
                }
            }
        }
        $validated['overlay_enabled'] = $request->boolean('overlay_enabled');
        $validated['recording_enabled'] = $request->boolean('recording_enabled');
        $livestream->update($validated);
        $redirect = redirect()->route('admin.livestreams.index')->with('success', 'Livestream updated.');
        if (!empty($streamKeyWarning)) {
            $redirect->with('warning', $streamKeyWarning);
        }
        return $redirect;
    }

    public function destroy(Livestream $livestream)
    {
        if ($livestream->status === 'live') {
            return redirect()->route('admin.livestreams.index')->with('error', 'End the stream first.');
        }
        $livestream->delete();
        return redirect()->route('admin.livestreams.index')->with('success', 'Livestream deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        } else {
            $ids = array_filter((array) ($ids ?? []));
        }
        if (empty($ids)) {
            return redirect()->route('admin.livestreams.index')->with('error', 'No livestreams selected.');
        }
        $deleted = Livestream::whereIn('id', $ids)->whereIn('status', ['scheduled', 'ended'])->delete();
        return redirect()->route('admin.livestreams.index')->with('success', $deleted ? "{$deleted} livestream(s) deleted." : 'No livestreams deleted (only scheduled/ended can be deleted).');
    }

    /**
     * Broadcast panel: RTMP connection details (RTLS URL + stream key), OBS setup, connection status.
     * For RTMP streams, generates stream key via Agora RTLS API if not yet set.
     */
    public function broadcast(Livestream $livestream)
    {
        $streamKeyError = null;
        if ($livestream->broadcast_type === Livestream::BROADCAST_TYPE_RTMP
            && empty($livestream->rtmp_stream_key)) {
            try {
                $rtls = app(AgoraRtlsService::class);
                $livestream->rtmp_stream_key = $rtls->createStreamKey($livestream->agora_channel, (string) $livestream->id, 0);
                if (empty($livestream->rtmp_url)) {
                    $livestream->rtmp_url = Livestream::defaultRtmpUrlForChannel($livestream->agora_channel);
                }
                $livestream->save();
            } catch (\Throwable $e) {
                $streamKeyError = $e->getMessage();
                if (empty($livestream->rtmp_url)) {
                    $livestream->rtmp_url = Livestream::defaultRtmpUrlForChannel($livestream->agora_channel);
                    $livestream->save();
                }
            }
        }
        return view('admin.livestreams.broadcast', compact('livestream', 'streamKeyError'));
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
