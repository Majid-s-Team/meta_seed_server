@extends('admin.layouts.app')

@section('title', 'Edit Livestream')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.livestreams.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Livestreams</a>
    <h1 class="admin-page-title mt-1">Edit Livestream</h1>
</div>

<div class="admin-card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.livestreams.update', $livestream) }}">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $livestream->title) }}" required class="admin-input">
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Description</label>
                <textarea name="description" rows="3" class="admin-input">{{ old('description', $livestream->description) }}</textarea>
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Thumbnail URL</label>
                <input type="text" name="thumbnail" value="{{ old('thumbnail', $livestream->thumbnail) }}" class="admin-input">
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Scheduled at *</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $livestream->scheduled_at?->format('Y-m-d\TH:i')) }}" required class="admin-input">
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Agora channel name *</label>
                <input type="text" name="agora_channel" value="{{ old('agora_channel', $livestream->agora_channel) }}" required class="admin-input font-mono">
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Broadcast type</label>
                <select name="broadcast_type" class="admin-input w-full">
                    <option value="agora_rtc" {{ old('broadcast_type', $livestream->broadcast_type ?? 'agora_rtc') === 'agora_rtc' ? 'selected' : '' }}>Agora RTC</option>
                    <option value="rtmp" {{ old('broadcast_type', $livestream->broadcast_type ?? '') === 'rtmp' ? 'selected' : '' }}>RTMP (OBS)</option>
                </select>
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">RTMP URL (for OBS)</label>
                <input type="text" name="rtmp_url" value="{{ old('rtmp_url', $livestream->rtmp_url) }}" placeholder="rtmp://..." class="admin-input font-mono text-sm">
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">RTMP stream key (for OBS)</label>
                <input type="text" name="rtmp_stream_key" value="{{ old('rtmp_stream_key', $livestream->rtmp_stream_key) }}" class="admin-input font-mono text-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="overlay_enabled" value="0">
                <input type="checkbox" name="overlay_enabled" value="1" {{ old('overlay_enabled', $livestream->overlay_enabled ?? false) ? 'checked' : '' }} class="rounded border-[var(--meta-border)]">
                <label class="text-[var(--meta-text-secondary)] text-sm">Enable overlay</label>
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Scoreboard / overlay URL</label>
                <input type="text" name="scoreboard_overlay_url" value="{{ old('scoreboard_overlay_url', $livestream->scoreboard_overlay_url) }}" class="admin-input text-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="recording_enabled" value="0">
                <input type="checkbox" name="recording_enabled" value="1" {{ old('recording_enabled', $livestream->recording_enabled ?? false) ? 'checked' : '' }} class="rounded border-[var(--meta-border)]">
                <label class="text-[var(--meta-text-secondary)] text-sm">Recording enabled</label>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Price (coins) *</label>
                    <input type="number" name="price" value="{{ old('price', $livestream->price) }}" min="0" step="0.01" required class="admin-input">
                </div>
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Max participants *</label>
                    <input type="number" name="max_participants" value="{{ old('max_participants', $livestream->max_participants) }}" min="1" required class="admin-input">
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">Update</button>
            <a href="{{ route('admin.livestreams.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
