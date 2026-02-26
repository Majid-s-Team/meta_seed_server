@extends('admin.layouts.app')

@section('title', 'Recordings')
@section('breadcrumb_page', 'Recordings')

@section('content')
<div class="animate-fade-in">
    <div class="page-header">
        <div>
            <p class="page-eyebrow">Recordings</p>
            <h1 class="page-title">Past Event Recordings</h1>
            <p class="admin-page-desc">Upload and manage past event videos for app users</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.recordings.create') }}" class="admin-btn-primary">
                <i data-lucide="plus"></i>
                Add Recording
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    {{-- Table card (Section 9 + 12 + 23: card-header, admin-table, td-secondary, badge) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Recording list</h3>
            <div class="card-header-actions"></div>
        </div>
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
                    <th>Title</th>
                    <th>Event</th>
                    <th>Recorded</th>
                    <th>Visible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recordings as $r)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $r->id }}" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                        <td class="font-medium text-white">{{ $r->title }}</td>
                        <td class="td-secondary">{{ $r->event?->title ?? '—' }}</td>
                        <td class="td-secondary">{{ $r->recorded_at?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            @if($r->is_visible)
                                <span class="badge badge-active">Yes</span>
                            @else
                                <span class="badge badge-inactive">No</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.recordings.edit', $r) }}" class="action-edit">Edit</a>
                                <span class="action-sep">|</span>
                                <form action="{{ route('admin.recordings.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Delete this recording?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-delete bg-transparent border-0 cursor-pointer p-0">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            @include('admin.partials.empty', ['icon' => 'video', 'title' => 'No recordings yet', 'description' => 'Add past event videos so app users can watch them.'])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($recordings->hasPages())
            <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $recordings->links('admin.partials.pagination') }}</div>
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
