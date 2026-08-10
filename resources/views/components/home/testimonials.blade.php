@props([
    'testimonials' => collect(),
    'section' => null,
])

@if ($testimonials->isNotEmpty())
    <div class="flat-testimonial flat-section overflow-hidden">
        <div class="container">
            <x-ui.section-title :title="$section?->heading ?? 'What travellers say'"
                :subtitle="$section?->subtitle ?? 'Verified reviews from people who took the trip.'" />

            <div class="swiper tf-sw-testimonial wow animate__animated animate__fadeIn"
                data-gt-swiper="testimonial" data-preview="3" data-tablet="2" data-mobile="1" data-space="30">
                <div class="swiper-wrapper">
                    @foreach ($testimonials as $review)
                        <div class="swiper-slide">
                            <div class="gt-testimonial">
                                <x-ui.rating :value="$review->rating" :show-score="false" />
                                <p class="gt-testimonial__body">{{ Str::limit($review->body, 240) }}</p>
                                <div class="gt-testimonial__author">
                                    <img src="{{ $review->avatar_url }}" alt="{{ $review->author_name }}"
                                        class="gt-testimonial__avatar" loading="lazy">
                                    <div>
                                        <div class="name h5">{{ $review->author_name }}</div>
                                        <a href="{{ route('tours.show', $review->tour) }}" class="subtitle text-color">
                                            {{ $review->tour->title }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="slider-control">
                    <a class="flex-prev nav-prev-location" role="button" aria-label="Previous">
                        <img src="{{ asset('assets/images/icons/arrow-left-1.svg') }}" alt="">
                    </a>
                    <div class="sw-pagination sw-pagination-tes text-center"></div>
                    <a class="flex-next nav-next-location" role="button" aria-label="Next">
                        <img src="{{ asset('assets/images/icons/arrow-right-2.svg') }}" alt="">
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
