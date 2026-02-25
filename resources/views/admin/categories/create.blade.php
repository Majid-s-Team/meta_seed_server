@extends('admin.layouts.app')

@section('title', 'Add Category')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.categories.index') }}" class="section-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Categories</a>
            <h1 class="admin-page-title mt-1">Add Category</h1>
            <p class="admin-page-desc">Create a new event category.</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    <div class="admin-card p-6 max-w-md">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="form-group">
                <label for="name" class="form-label">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="admin-input" placeholder="e.g. Sports, Concert">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="admin-btn-primary">Create Category</button>
                <a href="{{ route('admin.categories.index') }}" class="admin-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
