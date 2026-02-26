@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="animate-fade-in">
    <div class="page-header">
        <div>
            <a href="{{ route('admin.categories.index') }}" class="page-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Categories</a>
            <h1 class="page-title">Edit Category</h1>
            <p class="admin-page-desc">Update this event category.</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    <div class="admin-card p-6 max-w-md">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name" class="form-label">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required class="admin-input">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="admin-btn-primary">Update Category</button>
                <a href="{{ route('admin.categories.index') }}" class="admin-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
