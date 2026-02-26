@extends('admin.layouts.app')

@section('title', 'Event Categories')
@section('breadcrumb_page', 'Categories')

@section('content')
<div class="animate-fade-in">
    <div class="page-header">
        <div>
            <p class="page-eyebrow">Categories</p>
            <h1 class="page-title">Event Categories</h1>
            <p class="admin-page-desc">Manage categories for events</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">
                <i data-lucide="plus"></i>
                Add Category
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    {{-- Table card (Section 9 + 23: card-header, admin-table, td-secondary) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Category list</h3>
            <div class="card-header-actions"></div>
        </div>
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
                        <td class="td-secondary">{{ $cat->events_count }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="action-edit">Edit</a>
                                <span class="action-sep">|</span>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category? Events must be reassigned first.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-delete bg-transparent border-0 cursor-pointer p-0">Delete</button>
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
    <div class="filter-bar mt-3 flex gap-2" id="bulkActions" style="display: none;">
        <button type="button" onclick="submitBulkDelete()" class="admin-btn-ghost text-red-400 hover:bg-red-500/10">Delete selected</button>
    </div>
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
