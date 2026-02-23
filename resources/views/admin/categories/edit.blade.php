@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.categories.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Categories</a>
    <h1 class="admin-page-title mt-1">Edit Category</h1>
</div>

<div class="admin-card p-6 max-w-md">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Name *</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="admin-input">
            @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">Update Category</button>
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
