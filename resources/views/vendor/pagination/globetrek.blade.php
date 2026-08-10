@if ($paginator->hasPages())
    <div class="text-center mt-10 wow animate__animated animate__fadeInUp">
        <ul class="justify-content-center wd-navigation">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="nav-item disabled"><i class="icon icon-CaretLeft"></i></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="nav-item" rel="prev" aria-label="Previous page">
                        <i class="icon icon-CaretLeft"></i>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="nav-item disabled">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="nav-item active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="nav-item">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="nav-item" rel="next" aria-label="Next page">
                        <i class="icon icon-CaretRight"></i>
                    </a>
                @else
                    <span class="nav-item disabled"><i class="icon icon-CaretRight"></i></span>
                @endif
            </li>
        </ul>
    </div>
@endif
