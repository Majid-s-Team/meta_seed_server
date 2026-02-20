@extends('admin.layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.events.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Events</a>
    <h1 class="admin-page-title mt-1">Edit Event</h1>
</div>
@if($errors->any())
<div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<div class="admin-card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.events.update', $event) }}">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $event->title) }}" required class="admin-input">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Category *</label>
                <select name="category_id" required class="admin-input">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $event->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Description</label>
                <textarea name="description" rows="3" class="admin-input">{{ old('description', $event->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Date *</label>
                    <input type="date" name="date" value="{{ old('date', $event->date) }}" required class="admin-input">
                </div>
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Time *</label>
                    <input type="text" name="time" value="{{ old('time', $event->time) }}" required class="admin-input">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Total seats *</label>
                    <input type="number" name="total_seats" value="{{ old('total_seats', $event->total_seats) }}" min="1" required class="admin-input">
                </div>
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Available seats *</label>
                    <input type="number" name="available_seats" value="{{ old('available_seats', $event->available_seats) }}" min="0" required class="admin-input">
                </div>
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Coins *</label>
                    <input type="number" name="coins" value="{{ old('coins', $event->coins) }}" min="0" required class="admin-input">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-meta-secondary">
                    <input type="hidden" name="is_online" value="0">
                    <input type="checkbox" name="is_online" value="1" {{ old('is_online', $event->is_online) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#6A5CFF]">
                    Online event
                </label>
                <div>
                    <label class="block text-meta-secondary text-sm mb-1">Status</label>
                    <select name="status" class="admin-input w-auto">
                        <option value="active" {{ old('status', $event->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $event->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-meta-secondary text-sm mb-1">Cover image URL</label>
                <input type="text" name="cover_image" value="{{ old('cover_image', $event->cover_image) }}" class="admin-input">
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">Update Event</button>
            <a href="{{ route('admin.events.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
