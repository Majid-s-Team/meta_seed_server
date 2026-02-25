@extends('admin.layouts.app')

@section('title', 'Livestreams')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <p class="section-eyebrow">Livestreams</p>
            <h1 class="admin-page-title mt-1">Livestreams</h1>
            <p class="admin-page-desc">Schedule and control live streams</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.livestreams.create') }}" class="admin-btn-primary">
                <i data-lucide="radio"></i>
                Schedule Stream
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning mb-4">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    {{-- Filters (Section 11: form-group, form-label, admin-input) --}}
    <form method="GET" class="flex flex-wrap items-end gap-4 mb-5">
        <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="admin-input w-auto min-w-[140px]">
                <option value="">All statuses</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>Live</option>
                <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="admin-btn-ghost">Filter</button>
        </div>
    </form>

    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.livestreams.bulk-delete') }}" class="hidden">
        @csrf
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>

    {{-- Table card (Section 9 + 12 + 23: card-header, admin-table, td-secondary, badge) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Livestream list</h3>
            <div class="card-header-actions"></div>
        </div>
        <table class="admin-table w-full">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
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
                    <td><input type="checkbox" name="ids[]" value="{{ $ls->id }}" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]" {{ $ls->status === 'live' ? 'disabled' : '' }}></td>
                    <td class="font-medium text-white">{{ $ls->title }}</td>
                    <td class="td-secondary">{{ $ls->scheduled_at?->format('M d, H:i') }}</td>
                    <td class="td-secondary font-mono text-xs">{{ $ls->agora_channel }}</td>
                    <td class="td-secondary">{{ $ls->price }}</td>
                    <td class="td-secondary">{{ $ls->max_participants }}</td>
                    <td class="td-secondary">{{ $ls->current_viewer_count ?? 0 }}</td>
                    <td>
                        @php $health = $ls->stream_health_status ?? $ls->stream_health; @endphp
                        @if($health === 'waiting_for_broadcaster')
                            <span class="badge badge-pending">Waiting</span>
                        @elseif($health === 'live_receiving_feed' || $ls->stream_health === 'ok')
                            <span class="badge badge-active">Live</span>
                        @elseif($health === 'stream_offline' || $ls->stream_health === 'offline')
                            <span class="badge badge-inactive">Offline</span>
                        @elseif($ls->stream_health === 'degraded')
                            <span class="badge badge-pending">Degraded</span>
                        @else
                            <span class="badge badge-inactive">—</span>
                        @endif
                    </td>
                    <td class="td-secondary text-emerald-400 text-xs">{{ $ls->revenue_earned ? number_format((float)$ls->revenue_earned, 2) . ' coins' : '—' }}</td>
                    <td>
                        @if($ls->status === 'live')
                            <span class="badge badge-active">Live</span>
                        @elseif($ls->status === 'scheduled')
                            <span class="badge badge-info">Scheduled</span>
                        @else
                            <span class="badge badge-inactive">Ended</span>
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
                    <td colspan="11">
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
    <div class="mt-3 flex gap-2" id="bulkActions" style="display: none;">
        <button type="button" onclick="submitBulkDelete()" class="admin-btn-ghost text-red-400 hover:bg-red-500/10">Delete selected (scheduled/ended only)</button>
    </div>
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select:not([disabled])').forEach(cb => { cb.checked = this.checked; });
    toggleBulkActions();
});
document.querySelectorAll('.row-select').forEach(cb => { cb.addEventListener('change', toggleBulkActions); });
function toggleBulkActions() {
    const checked = document.querySelectorAll('.row-select:checked');
    document.getElementById('bulkActions').style.display = checked.length ? 'flex' : 'none';
}
function submitBulkDelete() {
    const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(c => c.value);
    if (!ids.length) return;
    if (!confirm('Delete ' + ids.length + ' selected livestream(s)? Only scheduled/ended streams will be deleted.')) return;
    document.getElementById('bulkDeleteIds').value = ids.join(',');
    document.getElementById('bulkDeleteForm').submit();
}
</script>
@endsection
