@extends('admin.layouts.app')

@section('title', 'Event Bookings')

@section('content')
<div class="mb-6 flex justify-between items-center animate-fade-in">
    <div>
        <a href="{{ route('admin.bookings.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Bookings</a>
        <h1 class="admin-page-title mt-1">Event Bookings</h1>
    </div>
    <a href="{{ route('admin.bookings.event.export', request()->only(['event_id','date'])) }}" class="admin-btn-ghost inline-flex items-center gap-2">
        <i data-lucide="download" class="w-4 h-4"></i>
        Export CSV
    </a>
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="event_id" class="admin-input w-auto min-w-[200px]">
        <option value="">All events</option>
        @foreach($events as $e)
            <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>{{ $e->title }} ({{ $e->date }})</option>
        @endforeach
    </select>
    <input type="date" name="date" value="{{ request('date') }}" class="admin-input w-auto">
    <button type="submit" class="admin-btn-ghost">Filter</button>
</form>

@if(isset($filteredRevenue))
<div class="mb-5 admin-card p-5">
    <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Revenue for this event</p>
    <p class="text-2xl font-bold text-white mt-1">{{ number_format($filteredRevenue['revenue']) }} coins</p>
    <p class="text-[var(--meta-text-muted)] text-xs mt-1">{{ $filteredRevenue['tickets'] }} tickets sold</p>
</div>
@endif

<div class="admin-card overflow-hidden">
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
                    <td class="text-[var(--meta-text-secondary)]">{{ $b->event->title ?? '-' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $b->event->date ?? '-' }} {{ $b->event->time ?? '' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
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
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
