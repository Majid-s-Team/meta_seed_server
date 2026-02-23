@extends('admin.layouts.app')

@section('title', 'Edit Recording')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.recordings.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Recordings</a>
    <h1 class="admin-page-title mt-1">Edit Recording</h1>
</div>

<div class="admin-card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.recordings.update', $recording) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $recording->title) }}" required class="admin-input">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Link to event (optional)</label>
                <select name="event_id" class="admin-input">
                    <option value="">— None —</option>
                    @foreach($events as $e)
                        <option value="{{ $e->id }}" {{ old('event_id', $recording->event_id) == $e->id ? 'selected' : '' }}>{{ $e->title }} ({{ $e->date }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Description</label>
                <textarea name="description" rows="3" class="admin-input">{{ old('description', $recording->description) }}</textarea>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Video</label>
                <p class="text-[var(--meta-text-muted)] text-xs mb-1">Upload new file to replace, or leave empty to keep current. Or set URL.</p>
                <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="admin-input block w-full text-sm text-[var(--meta-text-secondary)] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white/10 file:text-white">
                <input type="url" name="video_url" value="{{ old('video_url', $recording->video_url) }}" placeholder="Or video URL" class="admin-input mt-2">
                @error('video_file')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Thumbnail (optional)</label>
                <input type="file" name="thumbnail_file" accept="image/*" class="admin-input block w-full text-sm text-[var(--meta-text-secondary)] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white/10 file:text-white">
                <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url', $recording->thumbnail_url) }}" placeholder="Or thumbnail URL" class="admin-input mt-2">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Recorded date</label>
                    <input type="date" name="recorded_at" value="{{ old('recorded_at', $recording->recorded_at?->format('Y-m-d')) }}" class="admin-input">
                </div>
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Sort order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $recording->sort_order) }}" min="0" class="admin-input">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_visible" value="0">
                <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $recording->is_visible) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#6A5CFF]">
                <label class="text-[var(--meta-text-secondary)] text-sm">Visible to app users</label>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">Update Recording</button>
            <a href="{{ route('admin.recordings.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
