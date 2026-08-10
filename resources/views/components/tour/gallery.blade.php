@props(['tour'])

@php
    $photos = $tour->gallery;
    $visible = $photos->take(5);
    $hidden = $photos->skip(5);
    $id = 'tour-detail-'.$tour->id;
@endphp

<div class="gt-tour-hero wow animate__animated animate__fadeIn">
    @foreach ($visible as $index => $photo)
        <a href="{{ asset($photo) }}" class="gt-tour-hero__item" data-fancybox="{{ $id }}"
            aria-label="Open photo {{ $index + 1 }} of {{ $photos->count() }}">
            <img src="{{ asset($photo) }}" alt="{{ $tour->title }} — photo {{ $index + 1 }}"
                @if ($index > 0) loading="lazy" @endif>

            @if ($index === $visible->count() - 1 && $hidden->isNotEmpty())
                <span class="gt-tour-hero__more">+{{ $hidden->count() }} more</span>
            @endif
        </a>
    @endforeach

    {{-- Remaining photos stay in the lightbox set without rendering a tile. --}}
    @foreach ($hidden as $photo)
        <a href="{{ asset($photo) }}" data-fancybox="{{ $id }}" style="display:none" aria-hidden="true"></a>
    @endforeach
</div>
