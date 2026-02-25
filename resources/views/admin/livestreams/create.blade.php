@extends('admin.layouts.app')

@section('title', 'Schedule Livestream')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.livestreams.index') }}" class="section-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Livestreams</a>
            <h1 class="admin-page-title mt-1">Schedule Livestream</h1>
            <p class="admin-page-desc">Create a new livestream and set schedule, channel, and RTMP options.</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    <div class="admin-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.livestreams.store') }}">
            @csrf
            <div class="space-y-4">
                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="admin-input">
                    @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="admin-input">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="thumbnail" class="form-label">Thumbnail URL</label>
                    <input type="text" name="thumbnail" id="thumbnail" value="{{ old('thumbnail') }}" placeholder="https://..." class="admin-input">
                </div>
                <div class="form-group">
                    <label for="scheduled_at" class="form-label">Scheduled at *</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" required class="admin-input">
                </div>
                <div class="form-group">
                    <label for="agora_channel" class="form-label">Agora channel name *</label>
                    <input type="text" name="agora_channel" id="agora_channel" value="{{ old('agora_channel') }}" required class="admin-input font-mono">
                </div>
                <div class="form-group">
                    <label for="broadcast_type" class="form-label">Broadcast type</label>
                    <select name="broadcast_type" id="broadcast_type" class="admin-input w-full">
                        <option value="agora_rtc" {{ old('broadcast_type', 'agora_rtc') === 'agora_rtc' ? 'selected' : '' }}>Agora RTC (camera/app)</option>
                        <option value="rtmp" {{ old('broadcast_type') === 'rtmp' ? 'selected' : '' }}>RTMP (OBS/external encoder)</option>
                    </select>
                    <p class="text-[var(--meta-text-muted)] text-xs mt-1">RTMP: URL and stream key are auto-generated from channel (rtmp://push.agora.io/live/{channel}).</p>
                </div>
                <div class="form-group">
                    <label for="rtmp_url" class="form-label">RTMP URL (for OBS)</label>
                    <input type="text" name="rtmp_url" id="rtmp_url" value="{{ old('rtmp_url') }}" placeholder="Leave empty to auto-generate for RTMP" class="admin-input font-mono text-sm">
                </div>
                <div class="form-group">
                    <label for="rtmp_stream_key" class="form-label">RTMP stream key (for OBS)</label>
                    <input type="text" name="rtmp_stream_key" id="rtmp_stream_key" value="{{ old('rtmp_stream_key') }}" placeholder="Leave empty to use channel name" class="admin-input font-mono text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="overlay_enabled" value="0">
                    <input type="checkbox" name="overlay_enabled" value="1" {{ old('overlay_enabled') ? 'checked' : '' }} class="rounded border-[var(--meta-border)]" id="overlay_enabled">
                    <label for="overlay_enabled" class="text-[var(--meta-text-secondary)] text-sm">Enable overlay (browser source)</label>
                </div>
                <div class="form-group">
                    <label for="scoreboard_overlay_url" class="form-label">Scoreboard / overlay URL</label>
                    <input type="text" name="scoreboard_overlay_url" id="scoreboard_overlay_url" value="{{ old('scoreboard_overlay_url') }}" placeholder="https://..." class="admin-input text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="recording_enabled" value="0">
                    <input type="checkbox" name="recording_enabled" value="1" {{ old('recording_enabled') ? 'checked' : '' }} class="rounded border-[var(--meta-border)]" id="recording_enabled">
                    <label for="recording_enabled" class="text-[var(--meta-text-secondary)] text-sm">Recording enabled (structure only; engine TBD)</label>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="price" class="form-label">Price (coins) *</label>
                        <input type="number" name="price" id="price" value="{{ old('price', 0) }}" min="0" step="0.01" required class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="max_participants" class="form-label">Max participants *</label>
                        <input type="number" name="max_participants" id="max_participants" value="{{ old('max_participants', 100) }}" min="1" required class="admin-input">
                    </div>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="admin-btn-primary">Schedule</button>
                <a href="{{ route('admin.livestreams.index') }}" class="admin-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
