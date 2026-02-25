@if ($paginator->hasPages())
    <nav class="pagination-wrapper flex items-center justify-between gap-4 flex-wrap">
        <p class="text-[var(--meta-text-secondary)] text-sm">
            Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
        </p>
        <div class="flex gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="page-link disabled">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="page-link admin-btn-ghost text-sm transition">Prev</a>
            @endif
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-link active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-link admin-btn-ghost text-sm transition">{{ $page }}</a>
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="page-link admin-btn-ghost text-sm transition">Next</a>
            @else
                <span class="page-link disabled">Next</span>
            @endif
        </div>
    </nav>
@endif
