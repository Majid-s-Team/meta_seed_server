@extends('admin.layouts.app')

@section('title', 'Events')

@section('content')
<div class="flex justify-between items-center mb-6 animate-fade-in">
    <div>
        <h1 class="admin-page-title">Events</h1>
        <p class="admin-page-desc">Manage events and seats</p>
    </div>
    <a href="{{ route('admin.events.create') }}" class="admin-btn-primary">
        <i data-lucide="plus"></i>
        Add Event
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm animate-fade-in">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm animate-fade-in">{{ session('error') }}</div>
@endif

<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="status" class="admin-input w-auto min-w-[140px]">
        <option value="">All statuses</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
    </select>
    <select name="category_id" class="admin-input w-auto min-w-[160px]">
        <option value="">All categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="admin-btn-ghost">Filter</button>
</form>

<form id="bulkDeleteForm" method="POST" action="{{ route('admin.events.bulk-delete') }}" class="hidden">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>
<div class="admin-card overflow-hidden">
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
                    <td class="text-[var(--meta-text-secondary)]">{{ $event->category->name ?? '-' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $event->date }} {{ $event->time }}</td>
                    <td>{{ $event->total_seats - $event->available_seats }} sold / {{ $event->total_seats }}</td>
                    <td>{{ $event->coins }}</td>
                    <td>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium {{ $event->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : ($event->status === 'completed' ? 'bg-slate-500/20 text-slate-400' : 'bg-amber-500/20 text-amber-400') }}">{{ $event->status }}</span>
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
