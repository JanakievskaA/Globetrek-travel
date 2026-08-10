@props([
    'categories' => collect(),
    'section' => null,
])

<div class="flat-section flat-types">
    <div class="container">
        <x-ui.section-title :title="$section?->heading ?? 'Types of tours'"
            :subtitle="$section?->subtitle ?? 'Ten ways to travel, from dawn game drives to four-day treks. Pick the shape of the trip first.'" />

        <div class="wow animate__animated animate__fadeIn">
            <div class="swiper tf-sw-types" data-gt-swiper="types" data-preview="6" data-tablet="4"
                data-mobile="3" data-mobile-sm="2" data-space="30">
                <div class="swiper-wrapper">
                    @foreach ($categories as $category)
                        <div class="swiper-slide">
                            <a href="{{ route('tours.index', ['category' => $category->slug]) }}"
                                class="box-type style-03 no-bg">
                                <div class="icon-box">
                                    <img class="icon" src="{{ asset($category->image) }}"
                                        alt="{{ $category->name }}" loading="lazy">
                                </div>
                                <div class="content text-center">
                                    <div class="name h4">{{ $category->name }}</div>
                                    <p class="subtitle total-tour">
                                        {{ $category->tours_count }} {{ Str::plural('tour', $category->tours_count) }}
                                    </p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="slider-control">
                    <div class="sw-pagination sw-pagination-types text-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>
