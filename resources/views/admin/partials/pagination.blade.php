@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4 flex-wrap">
        <p class="text-[var(--meta-text-secondary)] text-sm">
            Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
        </p>
        <div class="flex gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 rounded-lg bg-white/5 text-[var(--meta-text-muted)] text-sm cursor-not-allowed">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 rounded-lg admin-btn-ghost text-sm transition">Prev</a>
            @endif
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="px-3 py-2 rounded-lg text-sm font-medium text-white" style="background: linear-gradient(135deg, var(--meta-accent-start) 0%, var(--meta-accent-end) 100%);">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-2 rounded-lg admin-btn-ghost text-sm transition">{{ $page }}</a>
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 rounded-lg admin-btn-ghost text-sm transition">Next</a>
            @else
                <span class="px-3 py-2 rounded-lg bg-white/5 text-[var(--meta-text-muted)] text-sm cursor-not-allowed">Next</span>
            @endif
        </div>
    </nav>
@endif
