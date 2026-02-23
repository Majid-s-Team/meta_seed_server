@extends('admin.layouts.app')

@section('title', 'Recordings')

@section('content')
<div class="flex justify-between items-center mb-6 animate-fade-in">
    <div>
        <h1 class="admin-page-title">Past Event Recordings</h1>
        <p class="admin-page-desc">Upload and manage past event videos for app users</p>
    </div>
    <a href="{{ route('admin.recordings.create') }}" class="admin-btn-primary">
        <i data-lucide="plus"></i>
        Add Recording
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
                    <td class="text-[var(--meta-text-secondary)]">{{ $r->event?->title ?? '—' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $r->recorded_at?->format('M j, Y') ?? '—' }}</td>
                    <td>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium {{ $r->is_visible ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">{{ $r->is_visible ? 'Yes' : 'No' }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.recordings.edit', $r) }}" class="text-[var(--meta-accent-end)] hover:underline text-sm font-medium">Edit</a>
                            <form action="{{ route('admin.recordings.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Delete this recording?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:underline text-sm">Delete</button>
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
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
