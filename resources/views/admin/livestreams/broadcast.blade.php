@extends('admin.layouts.app')

@section('title', 'Broadcast Setup — ' . $livestream->title)

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.livestreams.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Livestreams</a>
    <h1 class="admin-page-title mt-1">Broadcast Setup</h1>
    <p class="text-[var(--meta-text-secondary)] text-sm mt-1">{{ $livestream->title }}</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

@php
    $rtmpUrl = $livestream->rtmp_url ?: \App\Models\Livestream::defaultRtmpUrlForChannel($livestream->agora_channel);
    $streamKey = $livestream->rtmp_stream_key ?: $livestream->agora_channel;
    $healthStatus = $livestream->stream_health_status ?? $livestream->stream_health;
    $isWaiting = in_array($healthStatus, ['waiting_for_broadcaster', \App\Models\Livestream::STREAM_HEALTH_STATUS_WAITING], true);
@endphp

@if($livestream->status === 'live' && $isWaiting)
    <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-200 text-sm flex items-start gap-3">
        <i data-lucide="clock" class="w-5 h-5 shrink-0 mt-0.5"></i>
        <div>
            <strong>Waiting for broadcaster</strong> — Stream is set to Live. Start your encoder (OBS) with the RTMP URL and Stream Key below. When the feed is detected, status will change to "Live & receiving feed" automatically.
        </div>
    </div>
@endif

<div class="grid gap-6 max-w-4xl">
    {{-- RTMP connection details with copy buttons --}}
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="radio" class="w-5 h-5 text-[var(--meta-accent-end)]"></i>
            RTMP connection details
        </h2>
        <div class="space-y-3 font-mono text-sm">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <span class="text-[var(--meta-text-muted)]">Server URL</span>
                    <button type="button" class="copy-btn px-2 py-1 rounded text-xs font-medium bg-[var(--meta-accent-end)]/20 text-[var(--meta-accent-end)] hover:bg-[var(--meta-accent-end)]/30 transition" data-copy="{{ $rtmpUrl }}" data-label="RTMP URL">Copy RTMP URL</button>
                </div>
                <div class="p-3 rounded-lg bg-black/30 text-white break-all" id="rtmp-url-value">{{ $rtmpUrl }}</div>
            </div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <span class="text-[var(--meta-text-muted)]">Stream key</span>
                    <button type="button" class="copy-btn px-2 py-1 rounded text-xs font-medium bg-[var(--meta-accent-end)]/20 text-[var(--meta-accent-end)] hover:bg-[var(--meta-accent-end)]/30 transition" data-copy="{{ $streamKey }}" data-label="Stream Key">Copy Stream Key</button>
                </div>
                <div class="p-3 rounded-lg bg-black/30 text-white" id="stream-key-value">{{ $streamKey }}</div>
            </div>
        </div>
    </div>

    {{-- Connection checklist --}}
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
            <i data-lucide="list-checks" class="w-5 h-5 text-[var(--meta-accent-end)]"></i>
            Connection checklist
        </h2>
        <ul class="space-y-2 text-sm">
            <li class="flex items-center gap-2 {{ $livestream->status === 'live' ? 'text-emerald-400' : 'text-[var(--meta-text-muted)]' }}">
                @if($livestream->status === 'live')<i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>@else<i data-lucide="circle" class="w-4 h-4 shrink-0"></i>@endif
                Stream set to Live in admin
            </li>
            <li class="flex items-center gap-2 text-[var(--meta-text-secondary)]">
                <i data-lucide="circle" class="w-4 h-4 shrink-0"></i>
                OBS (or encoder) configured with Server URL and Stream Key above
            </li>
            <li class="flex items-center gap-2 {{ in_array($healthStatus, ['live_receiving_feed', \App\Models\Livestream::STREAM_HEALTH_STATUS_LIVE, 'ok'], true) ? 'text-emerald-400' : 'text-[var(--meta-text-muted)]' }}">
                @if(in_array($healthStatus, ['live_receiving_feed', \App\Models\Livestream::STREAM_HEALTH_STATUS_LIVE, 'ok'], true))<i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>@else<i data-lucide="circle" class="w-4 h-4 shrink-0"></i>@endif
                Broadcaster connected (feed detected)
            </li>
        </ul>
    </div>

    {{-- OBS setup instructions --}}
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="video" class="w-5 h-5 text-[var(--meta-accent-end)]"></i>
            OBS setup instructions
        </h2>
        <ol class="list-decimal list-inside space-y-2 text-[var(--meta-text-secondary)] text-sm">
            <li>Open OBS Studio → <strong class="text-white">Settings</strong> → <strong class="text-white">Stream</strong>.</li>
            <li>Set <strong class="text-white">Service</strong> to <strong class="text-white">Custom...</strong>.</li>
            <li>Set <strong class="text-white">Server</strong> to the RTMP Server URL above (or use <strong class="text-white">Copy RTMP URL</strong>).</li>
            <li>Set <strong class="text-white">Stream Key</strong> to the Stream key above (or use <strong class="text-white">Copy Stream Key</strong>).</li>
            <li>Click <strong class="text-white">Start Streaming</strong> when the stream is set to Live in the admin panel.</li>
        </ol>
    </div>

    {{-- Connection status & stream health --}}
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="activity" class="w-5 h-5 text-[var(--meta-accent-end)]"></i>
            Connection status
        </h2>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <span class="text-[var(--meta-text-muted)] text-sm">Stream health</span>
                @if($isWaiting)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400">Waiting for broadcaster</span>
                @elseif($healthStatus === \App\Models\Livestream::STREAM_HEALTH_STATUS_LIVE || $livestream->stream_health === 'ok')
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-500/20 text-emerald-400">Live & receiving feed</span>
                @elseif($healthStatus === \App\Models\Livestream::STREAM_HEALTH_STATUS_OFFLINE || $livestream->stream_health === 'offline')
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-500/20 text-slate-400">Stream offline</span>
                @else
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-[var(--meta-text-muted)]/20 text-[var(--meta-text-muted)]">—</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[var(--meta-text-muted)] text-sm">Bitrate</span>
                <span class="text-white text-sm">{{ $livestream->stream_bitrate_kbps ? $livestream->stream_bitrate_kbps . ' kbps' : '—' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[var(--meta-text-muted)] text-sm">Uptime</span>
                <span class="text-white text-sm">{{ $livestream->stream_uptime_seconds ? gmdate('H:i:s', $livestream->stream_uptime_seconds) : '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Overlay (if enabled) --}}
    @if($livestream->overlay_enabled && $livestream->scoreboard_overlay_url)
        <div class="admin-card p-6">
            <h2 class="text-lg font-semibold text-white mb-2">Scoreboard / overlay URL</h2>
            <p class="text-[var(--meta-text-secondary)] text-sm mb-2">Use this URL in OBS as a Browser Source.</p>
            <div class="p-3 rounded-lg bg-black/30 text-white font-mono text-sm break-all">{{ $livestream->scoreboard_overlay_url }}</div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex gap-3">
        @if($livestream->status === 'scheduled')
            <form action="{{ route('admin.livestreams.go-live', $livestream) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="admin-btn-primary">Go Live</button>
            </form>
        @endif
        @if($livestream->status === 'live')
            <form action="{{ route('admin.livestreams.end-stream', $livestream) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition">End Stream</button>
            </form>
        @endif
        <a href="{{ route('admin.livestreams.edit', $livestream) }}" class="admin-btn-ghost">Edit stream</a>
    </div>
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.querySelectorAll('.copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var text = this.getAttribute('data-copy');
        var label = this.getAttribute('data-label') || 'Value';
        if (!text) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                var orig = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function() { btn.textContent = orig; }, 2000);
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            var orig = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = orig; }, 2000);
        }
    });
});
</script>
@endsection
