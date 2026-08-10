@props([
    'slides' => collect(),
    'destinations' => collect(),
    'categories' => collect(),
    'stats' => [],
])

{{-- Slides come from HomeController::heroSlides(): each is an array of image,
     eyebrow, title, summary and an optional Destination behind it. --}}
<div class="hero">
    <div class="slider-default">
        <div class="swiper mySwiper" data-gt-swiper="hero" data-preview="1" data-autoplay="true"
            data-loop="true" data-speed="1200">
            <div class="swiper-wrapper">
                @foreach ($slides as $slide)
                    @php $place = $slide['destination'] ?? null; @endphp

                    <div class="swiper-slide home-5">
                        <div class="box-banner" style="background-image:url('{{ asset($slide['image']) }}')">
                            <div class="container-2">
                                <div class="box-title">
                                    @if ($slide['eyebrow'])
                                        <div class="h3">{{ $slide['eyebrow'] }}</div>
                                    @endif
                                    <h1>{{ $slide['title'] }}</h1>
                                    @if ($slide['summary'])
                                        <p class="gt-hero__summary">{{ $slide['summary'] }}</p>
                                    @endif

                                    <div class="wrap-search-link">
                                        <x-home.search-form :destinations="$destinations" :categories="$categories" />
                                    </div>
                                </div>

                                {{-- The meta strip only makes sense for a slide tied to a destination. --}}
                                @if ($place)
                                    <div class="tf-meta">
                                        <div class="contact-info">
                                            <div class="d-flex item-center gap-8">
                                                <span class="text-uppercase">
                                                    {{ $place->tours_count }} {{ Str::plural('tour', $place->tours_count) }} here
                                                </span>
                                                @if ($place->best_season)
                                                    <div class="divider"></div>
                                                    <span class="text-uppercase">Best time: {{ $place->best_season }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="contact-info">
                                            <div class="d-flex item-center gap-8">
                                                <span class="text-uppercase">
                                                    {{ number_format($stats['travellers'] ?? 0) }} travellers booked
                                                </span>
                                                <div class="divider"></div>
                                                <a href="{{ route('destinations.show', $place) }}" class="gt-hero__link">
                                                    Explore {{ $place->name }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex-direction-nav style-hero-1">
                <a class="flex-prev style-nav-2" role="button" aria-label="Previous slide">
                    <img src="{{ asset('assets/images/icons/arrow-left-1.svg') }}" alt="">
                </a>
                <a class="flex-next style-nav-2" role="button" aria-label="Next slide">
                    <img src="{{ asset('assets/images/icons/arrow-right-2.svg') }}" alt="">
                </a>
            </div>
        </div>
    </div>
</div>
