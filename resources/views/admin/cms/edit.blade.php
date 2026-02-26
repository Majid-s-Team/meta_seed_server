@extends('admin.layouts.app')

@section('title', 'Edit ' . ucfirst($page->type))

@section('content')
<div class="animate-fade-in">
    <div class="page-header">
        <div>
            <a href="{{ route('admin.cms.index') }}" class="page-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← CMS</a>
            <h1 class="page-title">Edit {{ ucfirst($page->type) }}</h1>
            <p class="admin-page-desc">Update title and content for this page.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cms.preview', $page->type) }}" target="_blank" class="admin-btn-ghost text-sm">Preview</a>
        </div>
    </div>

    <div class="admin-card p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.cms.update', $page->type) }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required class="admin-input">
                </div>
                <div class="form-group">
                    <label for="content" class="form-label">Content *</label>
                    <textarea name="content" id="content" rows="16" class="admin-input font-mono text-sm resize-y">{{ old('content', $page->content) }}</textarea>
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
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
