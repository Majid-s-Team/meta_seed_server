@extends('admin.layouts.app')

@section('title', 'Add Event')

@section('content')
<div class="animate-fade-in">
    <div class="page-header">
        <div>
            <a href="{{ route('admin.events.index') }}" class="page-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Events</a>
            <h1 class="page-title">Add Event</h1>
            <p class="admin-page-desc">Create a new event and set seats, date, and visibility.</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    <div class="admin-card p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="admin-input">
                    @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="category_id" class="form-label">Category *</label>
                    <select name="category_id" id="category_id" required class="admin-input">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="admin-input">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="date" class="form-label">Date *</label>
                        <input type="date" name="date" id="date" value="{{ old('date') }}" required class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="time" class="form-label">Time *</label>
                        <input type="time" name="time" id="time" value="{{ old('time') }}" required class="admin-input">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="form-group">
                        <label for="total_seats" class="form-label">Total seats *</label>
                        <input type="number" name="total_seats" id="total_seats" value="{{ old('total_seats', 100) }}" min="1" required class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="available_seats" class="form-label">Available seats *</label>
                        <input type="number" name="available_seats" id="available_seats" value="{{ old('available_seats', 100) }}" min="0" required class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="coins" class="form-label">Coins (price) *</label>
                        <input type="number" name="coins" id="coins" value="{{ old('coins', 0) }}" min="0" required class="admin-input">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <input type="hidden" name="is_online" value="0">
                    <label class="flex items-center gap-2 text-meta-secondary">
                        <input type="checkbox" name="is_online" value="1" {{ old('is_online') ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#6A5CFF]">
                        Online event
                    </label>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="admin-input w-auto">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cover_image_file" class="form-label">Cover image</label>
                    <p class="text-[var(--meta-text-muted)] text-xs mb-1">Upload a file or paste a URL (URL is used if both provided)</p>
                    <input type="file" name="cover_image_file" id="cover_image_file" accept="image/*" class="admin-input block w-full text-sm text-[var(--meta-text-secondary)] file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white/10 file:text-white">
                    <input type="text" name="cover_image" id="cover_image" value="{{ old('cover_image') }}" placeholder="Or paste image URL (https://...)" class="admin-input mt-2">
                    @error('cover_image_file')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="admin-btn-primary">Create Event</button>
                <a href="{{ route('admin.events.index') }}" class="admin-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
