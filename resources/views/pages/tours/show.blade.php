<x-layouts.app :title="$tour->title" :description="$tour->summary">

    {{-- No `pt-0` here: the theme marks it !important, which would cancel the
         header offset and let the fixed header sit over the title. --}}
    <div class="py-16 tf-single gt-page-offset">
        <div class="container">
            <div class="box-info mb-16 wow animate__animated animate__fadeInUp">
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <x-ui.badge tone="info">{{ $tour->category->name }}</x-ui.badge>
                    @if ($tour->is_featured)
                        <x-ui.badge tone="warning">Featured</x-ui.badge>
                    @endif
                    @if ($tour->is_on_sale)
                        <x-ui.badge tone="danger">{{ $tour->discount_percent }}% off</x-ui.badge>
                    @endif
                </div>

                <h1 class="font-semibold">{{ $tour->title }}</h1>

                <div class="meta mt-6 d-flex justify-content-between flex-wrap gap-4">
                    <div class="inner-left d-flex align-items-center gap-4 flex-wrap">
                        <div class="rate d-flex align-items-center gap-2">
                            <x-ui.rating :value="$tour->rating_avg" :show-score="false" />
                            <span class="review">
                                {{ number_format($tour->rating_avg, 1) }}
                                ({{ number_format($tour->reviews_count) }} reviews)
                            </span>
                        </div>
                        <div class="destination d-flex align-items-center gap-2">
                            <img src="{{ asset('assets/images/icons/place.svg') }}" alt="" width="18">
                            <a href="{{ route('destinations.show', $tour->destination) }}">
                                {{ $tour->destination->full_name }}
                            </a>
                        </div>
                    </div>
                    <div class="inner-right d-flex align-items-center gap-4">
                        <button type="button" class="gt-wishlist-toggle btn-wished"
                            data-tour-id="{{ $tour->id }}" aria-label="Save to wishlist">
                            <img src="{{ asset('assets/images/icons/icon-wishlist.svg') }}" alt="" class="icon">
                        </button>
                        <span class="gt-hint">{{ number_format($tour->views) }} views</span>
                    </div>
                </div>
            </div>

            <x-tour.gallery :tour="$tour" />
        </div>
    </div>

    <div class="flat-property">
        <div class="container">
            <div class="row">
                <div class="col-xl-8">
                    {{-- Key facts --}}
                    <div class="gt-info-grid mb-10 wow animate__animated animate__fadeInUp">
                        @php
                            $facts = [
                                ['clock.svg', 'Duration', $tour->duration_label],
                                ['users.svg', 'Group size', 'Max '.$tour->group_size],
                                ['mount.svg', 'Difficulty', ucfirst($tour->difficulty)],
                                ['language.svg', 'Languages', implode(', ', array_slice($tour->languages ?? [], 0, 2))],
                            ];
                        @endphp
                        @foreach ($facts as [$icon, $label, $value])
                            <div class="gt-info-item">
                                <div class="gt-info-item__icon">
                                    <img src="{{ asset('assets/images/icons/'.$icon) }}" alt="">
                                </div>
                                <div>
                                    <div class="gt-info-item__label">{{ $label }}</div>
                                    <div class="gt-info-item__value">{{ $value }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="box-property-detail wow animate__animated animate__fadeInUp">
                        <div class="property-topic mb-6 h3">Tour overview</div>
                        <div class="gt-prose">
                            <p class="subtitle">{{ $tour->summary }}</p>
                            <p>{{ $tour->description }}</p>
                        </div>
                    </div>

                    @if ($tour->highlights)
                        <div class="box-property-detail wow animate__animated animate__fadeInUp">
                            <div class="property-topic mb-6 h3">Highlights</div>
                            <ul class="gt-list-check">
                                @foreach ($tour->highlights as $highlight)
                                    <li>{{ $highlight }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="box-property-detail wow animate__animated animate__fadeInUp">
                        <div class="property-topic mb-6 h3">What's included</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="h5 mb-3">Included</div>
                                <ul class="gt-list-check">
                                    @foreach ($tour->includes ?? [] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-6 mt-4 mt-md-0">
                                <div class="h5 mb-3">Not included</div>
                                <ul class="gt-list-check gt-list-check--exclude">
                                    @foreach ($tour->excludes ?? [] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        @if ($tour->amenities)
                            <div class="mt-6">
                                <div class="h5 mb-3">Amenities</div>
                                <div class="gt-tags">
                                    @foreach ($tour->amenities as $amenity)
                                        <span class="gt-tag">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($tour->itineraries->isNotEmpty())
                        <div class="box-property-detail wow animate__animated animate__fadeInUp">
                            <div class="property-topic mb-6 h3">
                                {{ $tour->duration_days > 0 ? 'Tour plan' : 'What you will do' }}
                            </div>
                            <x-tour.itinerary :tour="$tour" />
                        </div>
                    @endif

                    @if ($tour->latitude && $tour->longitude)
                        <div class="box-property-detail wow animate__animated animate__fadeInUp">
                            <div class="property-topic mb-6 h3">Where you'll be</div>
                            <div class="location-top mb-4 d-flex align-items-center gap-2">
                                <img src="{{ asset('assets/images/icons/place.svg') }}" alt="" width="18">
                                <span class="subtitle">{{ $tour->destination->full_name }}</span>
                            </div>
                            <div class="flat-map">
                                <iframe title="Map of {{ $tour->destination->name }}" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade" style="width:100%;height:380px;border:0;border-radius:16px"
                                    src="https://maps.google.com/maps?q={{ $tour->latitude }},{{ $tour->longitude }}&z=10&output=embed"></iframe>
                            </div>
                        </div>
                    @endif

                    @if ($tour->faqs)
                        <div class="box-property-detail wow animate__animated animate__fadeInUp">
                            <div class="property-topic mb-6 h3">Frequently asked questions</div>
                            <x-tour.faqs :faqs="$tour->faqs" />
                        </div>
                    @endif

                    <div class="box-property-detail wow animate__animated animate__fadeInUp">
                        <x-tour.reviews :tour="$tour" :reviews="$reviews" :breakdown="$ratingBreakdown" />
                    </div>

                    <x-tour.review-form :tour="$tour" />
                </div>

                <div class="col-xl-4">
                    <div class="sidebar-tour wow animate__animated animate__fadeInDown">
                        <x-tour.booking-widget :tour="$tour" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($relatedTours->isNotEmpty())
        <div class="flat-section flat-recommended">
            <div class="container">
                <x-ui.section-title title="You might also like"
                    :subtitle="'More tours in '.$tour->destination->name.' and similar experiences.'" />

                <div class="swiper tf-swiper-tour wow animate__animated animate__fadeIn" data-gt-swiper="related"
                    data-preview="3" data-tablet="2" data-mobile="1" data-space="30">
                    <div class="swiper-wrapper">
                        @foreach ($relatedTours as $related)
                            <div class="swiper-slide">
                                <x-tour.card :tour="$related" variant="wide" />
                            </div>
                        @endforeach
                    </div>
                    <div class="slider-control">
                        <div class="sw-pagination sw-pagination-related text-center"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
