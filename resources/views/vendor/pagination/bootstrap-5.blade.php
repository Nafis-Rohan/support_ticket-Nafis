@if ($paginator->hasPages())
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mb-0 gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
            <span class="page-link rounded px-3">Previous</span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link rounded px-3" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        </li>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
            <li class="page-item disabled"><span class="page-link rounded-circle">{{ $element }}</span></li>
            @endif
            @if (is_array($element))
            @foreach ($element as $page => $url)
            <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                @if ($page == $paginator->currentPage())
                <span class="page-link rounded-circle mx-1" style="min-width: 2.25rem; text-align: center;">{{ $page }}</span>
                @else
                <a class="page-link rounded-circle mx-1" href="{{ $url }}" style="min-width: 2.25rem; text-align: center;">{{ $page }}</a>
                @endif
            </li>
            @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link rounded px-3" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        </li>
        @else
        <li class="page-item disabled" aria-disabled="true">
            <span class="page-link rounded px-3">Next</span>
        </li>
        @endif
    </ul>
</nav>
@endif
