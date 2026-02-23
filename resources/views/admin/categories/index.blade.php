@extends('admin.layouts.app')

@section('title', 'Event Categories')

@section('content')
<div class="flex justify-between items-center mb-6 animate-fade-in">
    <div>
        <h1 class="admin-page-title">Event Categories</h1>
        <p class="admin-page-desc">Manage categories for events</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">
        <i data-lucide="plus"></i>
        Add Category
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div class="admin-card overflow-hidden">
    <table class="admin-table w-full">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
                <th>Name</th>
                <th>Events</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $cat)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $cat->id }}" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                    <td class="font-medium text-white">{{ $cat->name }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $cat->events_count }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="text-[var(--meta-accent-end)] hover:underline text-sm font-medium">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category? Events must be reassigned first.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:underline text-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        @include('admin.partials.empty', ['icon' => 'folder', 'title' => 'No categories yet', 'description' => 'Add a category to use when creating events.'])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<form id="bulkDeleteForm" method="POST" action="{{ route('admin.categories.bulk-delete') }}" class="hidden">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>
<div class="mt-3 flex gap-2" id="bulkActions" style="display: none;">
    <button type="button" onclick="submitBulkDelete()" class="admin-btn-ghost text-red-400 hover:bg-red-500/10">Delete selected</button>
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
    toggleBulkActions();
});
document.querySelectorAll('.row-select').forEach(cb => {
    cb.addEventListener('change', toggleBulkActions);
});
function toggleBulkActions() {
    const checked = document.querySelectorAll('.row-select:checked');
    document.getElementById('bulkActions').style.display = checked.length ? 'flex' : 'none';
}
function submitBulkDelete() {
    const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(c => c.value);
    if (!ids.length) return;
    if (!confirm('Delete ' + ids.length + ' selected categor' + (ids.length === 1 ? 'y' : 'ies') + '? (Only categories with no events will be deleted.)')) return;
    document.getElementById('bulkDeleteIds').value = ids.join(',');
    document.getElementById('bulkDeleteForm').submit();
}
</script>
@endsection
