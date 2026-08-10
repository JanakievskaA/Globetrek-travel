@props([
    'tours' => collect(),
    'section' => null,
])

<div class="flat-recommended gt-section">
    <div class="container">
        <x-ui.section-title :title="$section?->heading ?? 'Trending right now'"
            :subtitle="$section?->subtitle ?? 'Booked more than anything else in the last thirty days.'" />

        <div class="flat-tab-recommend">
            <div class="swiper tf-swiper-tour wow animate__animated animate__fadeIn" data-gt-swiper="tour"
                data-preview="3" data-tablet="2" data-mobile="1" data-space="30">
                <div class="swiper-wrapper">
                    @foreach ($tours as $tour)
                        <div class="swiper-slide">
                            <x-tour.card :tour="$tour" variant="wide" />
                        </div>
                    @endforeach
                </div>
                <div class="slider-control">
                    <div class="sw-pagination sw-pagination-tour text-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>
