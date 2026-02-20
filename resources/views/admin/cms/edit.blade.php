@extends('admin.layouts.app')

@section('title', 'Edit ' . ucfirst($page->type))

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.cms.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← CMS</a>
    <a href="{{ route('admin.cms.preview', $page->type) }}" target="_blank" class="ml-3 text-[var(--meta-accent-end)] hover:underline text-sm">Preview</a>
    <h1 class="admin-page-title mt-1">Edit {{ ucfirst($page->type) }}</h1>
</div>

<div class="admin-card p-6 max-w-4xl">
    <form method="POST" action="{{ route('admin.cms.update', $page->type) }}">
        @csrf
        @method('PUT')
        <div class="space-y-5">
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="admin-input">
            </div>
            <div>
                <label class="block text-[var(--meta-text-secondary)] text-sm font-medium mb-2">Content *</label>
                <textarea name="content" rows="16" class="admin-input font-mono text-sm resize-y">{{ old('content', $page->content) }}</textarea>
                <p class="text-[var(--meta-text-muted)] text-xs mt-2">HTML is allowed. Scripts and iframes are stripped for security.</p>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="admin-btn-primary">
                <i data-lucide="save"></i>
                Save
            </button>
            <a href="{{ route('admin.cms.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
