<x-layouts.app :title="$destination->full_name" :description="$destination->summary">

    <x-ui.page-title :title="$destination->name" :image="$destination->hero_image"
        :breadcrumbs="['Destinations' => route('destinations.index'), $destination->country => null]" />

    <div class="flat-section">
        <div class="container">
            <div class="row">
                <div class="col-xl-8">
                    <div class="gt-prose">
                        <div class="h2 mb-4">{{ $destination->name }}, {{ $destination->country }}</div>
                        <p class="subtitle">{{ $destination->summary }}</p>
                        <p>{{ $destination->description }}</p>
                    </div>

                    <div class="gt-info-grid mt-8">
                        @php
                            $facts = [
                                ['place.svg', 'Region', $destination->continent],
                                ['calendar.svg', 'Best season', $destination->best_season],
                                ['money.svg', 'Currency', $destination->currency],
                                ['language.svg', 'Language', $destination->language],
                                ['clock.svg', 'Timezone', $destination->timezone],
                                ['trip.svg', 'Tours', $stats['tours'].' available'],
                            ];
                        @endphp
                        @foreach ($facts as [$icon, $label, $value])
                            @continue(! $value)
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
                </div>

                <div class="col-xl-4">
                    <div class="gt-card">
                        <div class="h4 mb-4">At a glance</div>
                        <ul class="gt-stack">
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Tours available</span>
                                <strong>{{ $stats['tours'] }}</strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Average rating</span>
                                <strong>{{ $stats['rating'] ?: '—' }} / 5</strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Traveller reviews</span>
                                <strong>{{ number_format($stats['reviews']) }}</strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Tours from</span>
                                <strong>${{ number_format($stats['from']) }}</strong>
                            </li>
                        </ul>

                        @if ($categories->isNotEmpty())
                            <div class="h5 mt-6 mb-3">Tour types here</div>
                            <div class="gt-tags">
                                @foreach ($categories as $category)
                                    <a class="gt-tag"
                                        href="{{ route('tours.index', ['destination' => $destination->slug, 'category' => $category->slug]) }}">
                                        {{ $category->name }} ({{ $category->tours_count }})
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('tours.index', ['destination' => $destination->slug]) }}"
                            class="tf-btn primary hover-1 w-full mt-6"><span>See all {{ $stats['tours'] }} tours</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flat-section flat-recommended pt-0">
        <div class="container">
            <x-ui.section-title :title="'Tours in '.$destination->name"
                :subtitle="'Every departure we run here, rated by travellers who went.'" align="left" />

            @if ($tours->isEmpty())
                <x-ui.empty-state title="No tours listed here yet"
                    message="We are adding departures for this destination shortly.">
                    <a href="{{ route('tours.index') }}" class="tf-btn primary hover-1"><span>Browse all tours</span></a>
                </x-ui.empty-state>
            @else
                <div class="tf-grid-layout tf-col-3 lg-col-2 sm-col-1">
                    @foreach ($tours as $tour)
                        <x-tour.card :tour="$tour" class="wow animate__animated animate__fadeInUp" />
                    @endforeach
                </div>

                {{ $tours->links() }}
            @endif
        </div>
    </div>

    @if ($nearby->isNotEmpty())
        <div class="flat-section pt-0">
            <div class="container">
                <x-ui.section-title :title="'More in '.$destination->continent" align="left" />
                <div class="tf-destination gt-grid-3">
                    @foreach ($nearby as $item)
                        <x-destination.card :destination="$item" />
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
