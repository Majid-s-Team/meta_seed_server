@extends('admin.layouts.app')

@section('title', 'Event Bookings')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="section-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Bookings</a>
            <h1 class="admin-page-title mt-1">Event Bookings</h1>
            <p class="admin-page-desc">View and filter event ticket bookings.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bookings.event.export', request()->only(['event_id','date'])) }}" class="admin-btn-ghost inline-flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Filters (Section 11: form-group, form-label, admin-input) --}}
    <form method="GET" class="flex flex-wrap items-end gap-4 mb-5">
        <div class="form-group">
            <label for="event_id" class="form-label">Event</label>
            <select name="event_id" id="event_id" class="admin-input w-auto min-w-[200px]">
                <option value="">All events</option>
                @foreach($events as $e)
                    <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>{{ $e->title }} ({{ $e->date }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" id="date" value="{{ request('date') }}" class="admin-input w-auto">
        </div>
        <div class="form-group">
            <button type="submit" class="admin-btn-ghost">Filter</button>
        </div>
    </form>

    @if(isset($filteredRevenue))
    <div class="mb-5 admin-card stat-card p-5">
        <p class="stat-card-label">Revenue for this event</p>
        <p class="stat-card-value mt-1">{{ number_format($filteredRevenue['revenue']) }} coins</p>
        <p class="text-[var(--meta-text-muted)] text-xs mt-1">{{ $filteredRevenue['tickets'] }} tickets sold</p>
    </div>
    @endif

    {{-- Table card (Section 9 + 23: card-header, admin-table, td-secondary) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Booking list</h3>
            <div class="card-header-actions"></div>
        </div>
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
                    <th>User</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Booked at</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    <tr>
                        <td><input type="checkbox" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                        <td class="font-medium text-white">{{ $b->user->name ?? '-' }}</td>
                        <td class="td-secondary">{{ $b->event->title ?? '-' }}</td>
                        <td class="td-secondary">{{ $b->event->date ?? '-' }} {{ $b->event->time ?? '' }}</td>
                        <td class="td-secondary">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            @include('admin.partials.empty', ['icon' => 'clipboard-list', 'title' => 'No event bookings', 'description' => 'Bookings will appear here when users purchase tickets.'])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($bookings->hasPages())
            <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $bookings->links('admin.partials.pagination') }}</div>
        @endif
    </div>
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
