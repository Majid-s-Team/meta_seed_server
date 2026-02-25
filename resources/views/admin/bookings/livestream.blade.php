@extends('admin.layouts.app')

@section('title', 'Livestream Bookings')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="section-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Bookings</a>
            <h1 class="admin-page-title mt-1">Livestream Bookings</h1>
            <p class="admin-page-desc">View and filter livestream viewer bookings.</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    {{-- Filters (Section 11: form-group, form-label, admin-input) --}}
    <form method="GET" class="flex flex-wrap items-end gap-4 mb-5">
        <div class="form-group">
            <label for="livestream_id" class="form-label">Livestream</label>
            <select name="livestream_id" id="livestream_id" class="admin-input w-auto min-w-[220px]">
                <option value="">All livestreams</option>
                @foreach($livestreams as $ls)
                    <option value="{{ $ls->id }}" {{ request('livestream_id') == $ls->id ? 'selected' : '' }}>{{ $ls->title }} ({{ $ls->scheduled_at?->format('M d, H:i') }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="admin-btn-ghost">Filter</button>
        </div>
    </form>

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
                    <th>Livestream</th>
                    <th>Scheduled</th>
                    <th>Booked at</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    <tr>
                        <td><input type="checkbox" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                        <td class="font-medium text-white">{{ $b->user->name ?? '-' }}</td>
                        <td class="td-secondary">{{ $b->livestream->title ?? '-' }}</td>
                        <td class="td-secondary">{{ $b->livestream->scheduled_at?->format('M d, H:i') ?? '-' }}</td>
                        <td class="td-secondary">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            @include('admin.partials.empty', ['icon' => 'radio', 'title' => 'No livestream bookings', 'description' => 'Viewer bookings will appear here.'])
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
