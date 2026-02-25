@extends('admin.layouts.app')

@section('title', 'Events')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <p class="section-eyebrow">Events</p>
            <h1 class="admin-page-title mt-1">Events</h1>
            <p class="admin-page-desc">Manage events and seats</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.events.create') }}" class="admin-btn-primary">
                <i data-lucide="plus"></i>
                Add Event
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
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
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="admin-input w-auto min-w-[160px]">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="admin-btn-ghost">Filter</button>
        </div>
    </form>

    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.events.bulk-delete') }}" class="hidden">
        @csrf
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>

    {{-- Table card (Section 9 + 12 + 23: card-header, admin-table, td-secondary, badge) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Event list</h3>
            <div class="card-header-actions"></div>
        </div>
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date / Time</th>
                    <th>Seats</th>
                    <th>Coins</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $event->id }}" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                        <td class="font-medium text-white">{{ $event->title }}</td>
                        <td class="td-secondary">{{ $event->category->name ?? '-' }}</td>
                        <td class="td-secondary">{{ $event->date }} {{ $event->time }}</td>
                        <td class="td-secondary">{{ $event->total_seats - $event->available_seats }} sold / {{ $event->total_seats }}</td>
                        <td class="td-secondary">{{ $event->coins }}</td>
                        <td>
                            @if($event->status === 'active')
                                <span class="badge badge-active">{{ $event->status }}</span>
                            @elseif($event->status === 'inactive')
                                <span class="badge badge-pending">{{ $event->status }}</span>
                            @else
                                <span class="badge badge-inactive">{{ $event->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.events.edit', $event) }}" class="text-[var(--meta-accent-end)] hover:underline text-sm font-medium">Edit</a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Delete this event?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline text-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            @include('admin.partials.empty', ['icon' => 'calendar-days', 'title' => 'No events yet', 'description' => 'Create your first event to start selling tickets.'])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())
            <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $events->links('admin.partials.pagination') }}</div>
        @endif
    </div>
    <div class="mt-3 flex gap-2" id="bulkActions" style="display: none;">
        <button type="button" onclick="submitBulkDelete()" class="admin-btn-ghost text-red-400 hover:bg-red-500/10">Delete selected</button>
    </div>
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
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
    if (!confirm('Delete ' + ids.length + ' selected event(s)? Events with bookings will be skipped.')) return;
    document.getElementById('bulkDeleteIds').value = ids.join(',');
    document.getElementById('bulkDeleteForm').submit();
}
</script>
@endsection
