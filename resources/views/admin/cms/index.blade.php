@extends('admin.layouts.app')

@section('title', 'CMS')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <p class="section-eyebrow">CMS</p>
            <h1 class="admin-page-title mt-1">CMS</h1>
            <p class="admin-page-desc">Privacy, Terms, About, FAQ</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    @foreach($types as $type)
        @php $page = $pages->get($type); @endphp
        <a href="{{ route('admin.cms.edit', $type) }}" class="admin-card block p-6 transition-all duration-200 hover:border-[var(--meta-accent-start)]/30 group">
            <div class="flex items-start gap-4">
                <div class="admin-stat-icon bg-slate-500/20 text-slate-400 group-hover:bg-slate-500/30 transition">
                    <i data-lucide="file-text"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold text-white capitalize mb-1">{{ $type }}</h2>
                    <p class="text-[var(--meta-text-secondary)] text-sm">{{ Str::limit($page->content ?? 'No content', 80) }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-[var(--meta-text-muted)] flex-shrink-0"></i>
            </div>
        </a>
    @endforeach
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
