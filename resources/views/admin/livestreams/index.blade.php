@extends('admin.layouts.app')

@section('title', 'Livestreams')

@section('content')
<div class="flex justify-between items-center mb-6 animate-fade-in">
    <div>
        <h1 class="admin-page-title">Livestreams</h1>
        <p class="admin-page-desc">Schedule and control live streams</p>
    </div>
    <a href="{{ route('admin.livestreams.create') }}" class="admin-btn-primary">
        <i data-lucide="radio"></i>
        Schedule Stream
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="mb-4 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm">{{ session('warning') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<form method="GET" class="flex gap-2 mb-5">
    <select name="status" class="admin-input w-auto min-w-[140px]">
        <option value="">All statuses</option>
        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
        <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>Live</option>
        <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
    </select>
    <button type="submit" class="admin-btn-ghost">Filter</button>
</form>

<div class="admin-card overflow-hidden">
    <table class="admin-table w-full">
        <thead>
            <tr>
                <th>Title</th>
                <th>Scheduled</th>
                <th>Channel</th>
                <th>Price</th>
                <th>Max</th>
                <th>Viewers</th>
                <th>Health</th>
                <th>Revenue</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($livestreams as $ls)
                <tr>
                    <td class="font-medium text-white">{{ $ls->title }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $ls->scheduled_at?->format('M d, H:i') }}</td>
                    <td class="text-[var(--meta-text-muted)] font-mono text-xs">{{ $ls->agora_channel }}</td>
                    <td>{{ $ls->price }}</td>
                    <td>{{ $ls->max_participants }}</td>
                    <td>{{ $ls->current_viewer_count ?? 0 }}</td>
                    <td>
                        @php $health = $ls->stream_health_status ?? $ls->stream_health; @endphp
                        @if($health === 'waiting_for_broadcaster')
                            <span class="text-amber-400 text-xs">Waiting</span>
                        @elseif($health === 'live_receiving_feed' || $ls->stream_health === 'ok')
                            <span class="text-emerald-400 text-xs">Live</span>
                        @elseif($health === 'stream_offline' || $ls->stream_health === 'offline')
                            <span class="text-slate-400 text-xs">Offline</span>
                        @elseif($ls->stream_health === 'degraded')
                            <span class="text-amber-400 text-xs">Degraded</span>
                        @else
                            <span class="text-[var(--meta-text-muted)] text-xs">—</span>
                        @endif
                    </td>
                    <td class="text-emerald-400 text-xs">{{ $ls->revenue_earned ? number_format((float)$ls->revenue_earned, 2) . ' coins' : '—' }}</td>
                    <td>
                        @if($ls->status === 'live')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-[var(--meta-live)]/20 text-[var(--meta-live)]"><span class="w-1.5 h-1.5 rounded-full bg-[var(--meta-live)] animate-pulse"></span> Live</span>
                        @elseif($ls->status === 'scheduled')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-500/20 text-blue-400">Scheduled</span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-500/20 text-slate-400">Ended</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-2 items-center">
                            <a href="{{ route('admin.livestreams.broadcast', $ls) }}" class="text-[var(--meta-accent-end)] hover:underline text-sm font-medium">Broadcast</a>
                            @if(($ls->rtmp_url || $ls->rtmp_stream_key) && $ls->status === 'live')
                                <span class="text-[var(--meta-text-muted)] text-xs" title="RTMP ready">OBS</span>
                            @endif
                            @if($ls->status === 'scheduled')
                                <form action="{{ route('admin.livestreams.go-live', $ls) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--meta-live)]/20 text-[var(--meta-live)] hover:bg-[var(--meta-live)]/30 transition">Go Live</button>
                                </form>
                                <a href="{{ route('admin.livestreams.edit', $ls) }}" class="text-[var(--meta-accent-end)] hover:underline text-sm font-medium">Edit</a>
                            @endif
                            @if($ls->status === 'live')
                                <form action="{{ route('admin.livestreams.end-stream', $ls) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition">End Stream</button>
                                </form>
                            @endif
                            @if(in_array($ls->status, ['scheduled', 'ended']))
                                <form action="{{ route('admin.livestreams.destroy', $ls) }}" method="POST" class="inline" onsubmit="return confirm('Delete this livestream?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline text-sm">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        @include('admin.partials.empty', ['icon' => 'radio', 'title' => 'No livestreams yet', 'description' => 'Schedule a stream to start broadcasting.'])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($livestreams->hasPages())
        <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $livestreams->links('admin.partials.pagination') }}</div>
    @endif
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
