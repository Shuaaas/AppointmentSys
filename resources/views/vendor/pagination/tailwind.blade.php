@if ($paginator->hasPages())
    <div class="pagination-links">
        <span>
            @if ($paginator->firstItem())
                Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} result{{ $paginator->total() !== 1 ? 's' : '' }}
            @else
                No results found.
            @endif
        </span>
        <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
            <span>
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span>&laquo; Prev</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">&laquo; Prev</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span>{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">Next &raquo;</a>
                @else
                    <span class="disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span>Next &raquo;</span>
                    </span>
                @endif
            </span>
        </nav>
    </div>
@endif
