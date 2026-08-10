@props([
    'destination',
    'section' => null,
])

@if ($destination)
    @php
        $cheapest = $destination->publishedTours()->min('price');
        $rating = round((float) $destination->publishedTours()->avg('rating_avg'), 1);
    @endphp

    <div class="bg-three tf-vacation-hero">
        <div class="container">
            <div class="flat-img-with-text style-05">
                <div class="content-left">
                    <div class="box-map wow animate__animated animate__fadeInLeft">
                        <img src="{{ asset($section?->value('image') ?? $destination->hero_image ?? $destination->image) }}"
                            alt="{{ $destination->name }}" class="gt-spotlight__image">
                    </div>
                </div>
                <div class="content-right">
                    <x-ui.rating :value="$rating" :count="$destination->publishedTours()->sum('reviews_count')"
                        class="rating wow animate__animated animate__fadeInUp" />

                    <div class="box-title wow animate__animated animate__fadeInUp">
                        <div class="sub h3">Explore {{ $destination->country }}</div>
                        <h2 class="destination-name">{{ $destination->name }}</h2>
                    </div>

                    <div class="box-schedule wow animate__animated animate__fadeInUp">
                        <p class="desc subtitle">{{ $destination->summary }}</p>
                        <div class="calendar">
                            <div class="item departure">
                                <label>Region</label>
                                <div class="h3">{{ $destination->continent }}</div>
                            </div>
                            <div class="item persons">
                                <label>Best season</label>
                                <div class="h3">{{ $destination->best_season }}</div>
                            </div>
                            <div class="item total-money">
                                <label>{{ $destination->tours_count }} tours from</label>
                                <div class="h3">${{ number_format($cheapest ?? 0) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="wg-contact wow animate__animated animate__fadeInUp">
                        <div class="box-info">
                            <i class="icon-phone icon"></i>
                            <div class="content">
                                <div class="subtitle">{{ $section?->value('phone_label') ?? 'Speak to a specialist' }}</div>
                                @php $phone = $section?->value('phone') ?? '(229) 555-0109'; @endphp
                                <div class="phone h4">
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('destinations.show', $destination) }}" class="tf-btn primary hover-2">
                            <span>See {{ $destination->name }} tours</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
