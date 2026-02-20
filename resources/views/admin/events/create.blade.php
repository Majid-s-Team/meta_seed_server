@extends('admin.layouts.app')

@section('title', 'Add Event')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.events.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Events</a>
    <h1 class="admin-page-title mt-1">Add Event</h1>
</div>

<div class="admin-card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="admin-input">
                @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Category *</label>
                <select name="category_id" required class="admin-input">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Description</label>
                <textarea name="description" rows="3" class="admin-input">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Date *</label>
                    <input type="date" name="date" value="{{ old('date') }}" required class="admin-input">
                </div>
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Time *</label>
                    <input type="text" name="time" value="{{ old('time') }}" placeholder="e.g. 18:00" required class="admin-input">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Total seats *</label>
                    <input type="number" name="total_seats" value="{{ old('total_seats', 100) }}" min="1" required class="admin-input">
                </div>
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Available seats *</label>
                    <input type="number" name="available_seats" value="{{ old('available_seats', 100) }}" min="0" required class="admin-input">
                </div>
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Coins (price) *</label>
                    <input type="number" name="coins" value="{{ old('coins', 0) }}" min="0" required class="admin-input">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <input type="hidden" name="is_online" value="0">
                <label class="flex items-center gap-2 text-meta-secondary">
                    <input type="checkbox" name="is_online" value="1" {{ old('is_online') ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#6A5CFF]">
                    Online event
                </label>
                <div>
                    <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Status</label>
                    <select name="status" class="admin-input w-auto">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Cover image URL</label>
                <input type="text" name="cover_image" value="{{ old('cover_image') }}" placeholder="https://..." class="admin-input">
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">Create Event</button>
            <a href="{{ route('admin.events.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
