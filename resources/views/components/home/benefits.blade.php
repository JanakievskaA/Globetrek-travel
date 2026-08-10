@props([
    'stats' => [],
    'section' => null,
])

@php
    // Cards are editable under Homepage → Why travel with us; the registry
    // holds the four the site shipped with as the fallback.
    $cards = $section?->rows('cards') ?? \App\Support\PageSections::defaults('benefits')['data']['cards'];

    $subtitle = $section?->subtitle
        ?: 'A curated catalogue of '.($stats['tours'] ?? 0).' tours across '.($stats['destinations'] ?? 0)
            .' destinations, averaging '.($stats['rating'] ?? 0).' out of 5 from verified travellers.';
@endphp

<div class="flat-section pb-0 tf-benefit bg-white">
    <div class="container">
        <x-ui.section-title :title="$section?->heading ?? 'Why travel with GlobeTrek'" :subtitle="$subtitle" />

        <div class="swiper tf-swiper-device wow animate__animated animate__fadeIn wrap-benefit"
            data-gt-swiper="benefit" data-preview="4" data-tablet="2" data-mobile="1" data-space="30">
            <div class="swiper-wrapper">
                @foreach ($cards as $card)
                    <div class="swiper-slide">
                        <div class="gt-benefit">
                            @if (! empty($card['icon']))
                                <div class="gt-benefit__icon">
                                    <img src="{{ asset($card['icon']) }}" alt="">
                                </div>
                            @endif
                            <div class="gt-benefit__body">
                                <div class="h5">{{ $card['title'] ?? '' }}</div>
                                <p class="subtitle text-color">{{ $card['text'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="slider-control">
                <div class="sw-pagination sw-pagination-device text-center"></div>
            </div>
        </div>
    </div>
</div>
