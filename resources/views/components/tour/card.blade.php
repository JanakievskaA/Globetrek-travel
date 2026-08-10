@props([
    'tour',
    // grid  — 4-up card used on the homepage and grid layouts
    // list  — wide horizontal row used by the tour list page
    // wide  — taller card used inside carousels
    'variant' => 'grid',
])

@php
    $variantClass = match ($variant) {
        'list' => 'list-style-01',
        'wide' => 'list-style-02',
        default => '',
    };

    $galleryId = 'tour-gallery-'.$tour->id.'-'.$variant;
    $gallery = $tour->relationLoaded('images') ? $tour->gallery : collect([$tour->image]);
    $url = route('tours.show', $tour);
@endphp

<div {{ $attributes->merge(['class' => trim('item hover-img '.$variantClass)]) }}>
    <div class="archive-top">
        <div class="images-group img-style">
            <a href="{{ $url }}" class="image-link">
                <img src="{{ asset($tour->image) }}" alt="{{ $tour->title }}" loading="lazy">
            </a>
            <div class="group-meta">
                <div class="tag-meta">
                    @if ($tour->is_featured)
                        <div class="flag-tag">Featured</div>
                    @endif
                    @if ($tour->is_on_sale)
                        <div class="flag-tag gt-flag-sale">-{{ $tour->discount_percent }}%</div>
                    @endif

                    @if ($gallery->count() > 1)
                        <a href="javascript:void(0)" class="count image"
                            data-fancybox="{{ $galleryId }}" data-src="{{ asset($gallery->first()) }}"
                            aria-label="View {{ $gallery->count() }} photos">
                            <img src="{{ asset('assets/images/icons/picture.svg') }}" alt="" class="icon">
                            <span class="total-image">{{ $gallery->count() }}</span>
                        </a>
                        @foreach ($gallery->skip(1) as $photo)
                            <a data-fancybox="{{ $galleryId }}" data-src="{{ asset($photo) }}" style="display:none"></a>
                        @endforeach
                    @endif
                </div>
                <button type="button" class="btn-wished gt-wishlist-toggle" data-tour-id="{{ $tour->id }}"
                    aria-label="Save {{ $tour->title }} to wishlist">
                    <img src="{{ asset('assets/images/icons/icon-wishlist.svg') }}" alt="" class="icon">
                </button>
            </div>
        </div>
    </div>

    <div class="archive-bottom">
        <div class="content-top">
            @if ($variant === 'wide')
                <div class="address">
                    <img src="{{ asset('assets/images/icons/place.svg') }}" alt="" class="icon">
                    <span class="location">{{ $tour->destination->full_name }}</span>
                </div>
            @endif
            <x-ui.rating :value="$tour->rating_avg" :count="$tour->reviews_count" />
        </div>

        <div class="tour-title {{ $variant === 'wide' ? 'h3' : 'h5' }}">
            <a href="{{ $url }}" class="link multi-ellipsis">{{ $tour->title }}</a>
        </div>

        @if ($variant === 'list')
            <p class="gt-card-summary text-color">{{ Str::limit($tour->summary, 150) }}</p>
        @endif

        <div class="content-info-middle">
            <div class="info person">
                <img src="{{ asset('assets/images/icons/users.svg') }}" alt="" class="icon">
                <span class="total-people">Max {{ $tour->group_size }}</span>
            </div>
            <div class="info date">
                <img src="{{ asset('assets/images/icons/calendar.svg') }}" alt="" class="icon">
                <span class="total-day">{{ $tour->duration_label }}</span>
            </div>
            @if ($variant === 'list')
                <div class="info">
                    <img src="{{ asset('assets/images/icons/discovery.svg') }}" alt="" class="icon">
                    <span>{{ $tour->category->name }}</span>
                </div>
            @endif
        </div>

        <div class="content-bottom">
            <div class="address">
                <img src="{{ asset('assets/images/icons/place.svg') }}" alt="" class="icon">
                <span class="location">{{ $tour->destination->full_name }}</span>
            </div>
            <x-ui.price :tour="$tour" :suffix="$tour->duration_days > 0 ? '/person' : null" />
        </div>

        @if ($variant === 'list')
            <div class="gt-card-actions">
                <a href="{{ $url }}" class="tf-btn primary hover-1"><span>View tour</span></a>
            </div>
        @endif
    </div>
</div>
