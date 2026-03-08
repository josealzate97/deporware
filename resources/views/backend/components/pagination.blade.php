@if($paginator->hasPages())
    <div class="{{ $wrapperClass ?? 'section-pagination' }}">
        <nav aria-label="{{ $ariaLabel ?? 'Paginador' }}">
            <ul class="pagination mb-0">
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() ?? '#' }}" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span> Anterior
                    </a>
                </li>

                @php
                    $start = max($paginator->currentPage() - 2, 1);
                    $end = min($paginator->currentPage() + 2, $paginator->lastPage());
                @endphp

                @if($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if($start > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                @for($page = $start; $page <= $end; $page++)
                    <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor

                @if($end < $paginator->lastPage())
                    @if($end < $paginator->lastPage() - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                    </li>
                @endif

                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() ?? '#' }}" aria-label="Siguiente">
                        Siguiente <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
@endif
