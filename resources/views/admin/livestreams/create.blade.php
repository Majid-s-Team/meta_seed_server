@extends('admin.layouts.app')

@section('title', 'Schedule Livestream')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.livestreams.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Livestreams</a>
    <h1 class="admin-page-title mt-1">Schedule Livestream</h1>
</div>

<div class="admin-card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.livestreams.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="admin-input">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Description</label>
                <textarea name="description" rows="3" class="admin-input">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Thumbnail URL</label>
                <input type="text" name="thumbnail" value="{{ old('thumbnail') }}" placeholder="https://..." class="admin-input">
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Scheduled at *</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required class="admin-input">
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Agora channel name *</label>
                <input type="text" name="agora_channel" value="{{ old('agora_channel') }}" required class="admin-input font-mono">
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Broadcast type</label>
                <select name="broadcast_type" class="admin-input w-full">
                    <option value="agora_rtc" {{ old('broadcast_type', 'agora_rtc') === 'agora_rtc' ? 'selected' : '' }}>Agora RTC (camera/app)</option>
                    <option value="rtmp" {{ old('broadcast_type') === 'rtmp' ? 'selected' : '' }}>RTMP (OBS/external encoder)</option>
                </select>
                <p class="text-[var(--meta-text-muted)] text-xs mt-1">RTMP: URL and stream key are auto-generated from channel (rtmp://push.agora.io/live/{channel}).</p>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">RTMP URL (for OBS)</label>
                <input type="text" name="rtmp_url" value="{{ old('rtmp_url') }}" placeholder="Leave empty to auto-generate for RTMP" class="admin-input font-mono text-sm">
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">RTMP stream key (for OBS)</label>
                <input type="text" name="rtmp_stream_key" value="{{ old('rtmp_stream_key') }}" placeholder="Leave empty to use channel name" class="admin-input font-mono text-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="overlay_enabled" value="0">
                <input type="checkbox" name="overlay_enabled" value="1" {{ old('overlay_enabled') ? 'checked' : '' }} class="rounded border-[var(--meta-border)]">
                <label class="text-[var(--meta-text-secondary)] text-sm">Enable overlay (browser source)</label>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Scoreboard / overlay URL</label>
                <input type="text" name="scoreboard_overlay_url" value="{{ old('scoreboard_overlay_url') }}" placeholder="https://..." class="admin-input text-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="recording_enabled" value="0">
                <input type="checkbox" name="recording_enabled" value="1" {{ old('recording_enabled') ? 'checked' : '' }} class="rounded border-[var(--meta-border)]">
                <label class="text-[var(--meta-text-secondary)] text-sm">Recording enabled (structure only; engine TBD)</label>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Price (coins) *</label>
                    <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" required class="admin-input">
                </div>
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Max participants *</label>
                    <input type="number" name="max_participants" value="{{ old('max_participants', 100) }}" min="1" required class="admin-input">
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">Schedule</button>
            <a href="{{ route('admin.livestreams.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
