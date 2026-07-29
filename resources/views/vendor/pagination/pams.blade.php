@if ($paginator->total() > 0)
    @php
        $total = max(1, $paginator->lastPage());
        $current = max(1, $paginator->currentPage());
        $showEllipsis = $total > 6;
    @endphp

    <div class="pams-pagination">
        <span class="pams-count">
            @if ($paginator->firstItem())
                Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} result{{ $paginator->total() !== 1 ? 's' : '' }}
            @else
                No results found.
            @endif
        </span>
        <nav class="pams-nav" aria-label="Pagination Navigation">
            <button
                type="button"
                class="pams-btn pams-prev"
                @if ($paginator->onFirstPage()) disabled @endif
                aria-label="Previous page"
                @if (!$paginator->onFirstPage())
                    onclick="window.location='{{ $paginator->previousPageUrl() }}'"
                @endif
            >
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>

            <span class="pams-pages">
                @for ($i = 1; $i <= $total; $i++)
                    @if ($showEllipsis)
                        @if ($i == 1 || $i == $total || ($i >= $current - 1 && $i <= $current + 1))
                            @if ($i == $paginator->currentPage())
                                <span class="pams-page pams-active" aria-current="page">{{ $i }}</span>
                            @else
                                <a href="{{ $paginator->url($i) }}" class="pams-page" aria-label="Go to page {{ $i }}">{{ $i }}</a>
                            @endif
                        @elseif ($i == 2 && $current > 3)
                            <span class="pams-ellipsis" aria-hidden="true">…</span>
                        @elseif ($i == $total - 1 && $current < $total - 2)
                            <span class="pams-ellipsis" aria-hidden="true">…</span>
                        @endif
                    @else
                        @if ($i == $paginator->currentPage())
                            <span class="pams-page pams-active" aria-current="page">{{ $i }}</span>
                        @else
                            <a href="{{ $paginator->url($i) }}" class="pams-page" aria-label="Go to page {{ $i }}">{{ $i }}</a>
                        @endif
                    @endif
                @endfor
            </span>

            <button
                type="button"
                class="pams-btn pams-next"
                @if (!$paginator->hasMorePages()) disabled @endif
                aria-label="Next page"
                @if ($paginator->hasMorePages())
                    onclick="window.location='{{ $paginator->nextPageUrl() }}'"
                @endif
            >
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </nav>
    </div>
@endif
