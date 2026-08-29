@props(['paginator'])
@if ($paginator->hasPages())
    <div class="flex items-center justify-between border-t border-mv-border px-4 py-3 text-xs text-mv-text-secondary">
        <span>Exibindo {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} registros</span>
        <div class="flex gap-1">
            @if ($paginator->onFirstPage())
                <span class="rounded px-2 py-1 text-mv-text-muted">&laquo;</span>
            @else
                <a class="rounded bg-mv-surface2 px-2 py-1 text-mv-text hover:text-mv-text" href="{{ $paginator->previousPageUrl() }}">&laquo;</a>
            @endif
            <span class="rounded px-2 py-1 text-mv-text-muted">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a class="rounded bg-mv-surface2 px-2 py-1 text-mv-text hover:text-mv-text" href="{{ $paginator->nextPageUrl() }}">&raquo;</a>
            @else
                <span class="rounded px-2 py-1 text-mv-text-muted">&raquo;</span>
            @endif
        </div>
    </div>
@endif
