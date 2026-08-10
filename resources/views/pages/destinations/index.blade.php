<x-layouts.app title="Destinations"
    description="Browse every GlobeTrek destination — {{ $destinations->total() }} places across six continents, each with hand-picked small-group tours.">

    <x-ui.page-title title="Destinations" :breadcrumbs="['Destinations' => null]" />

    {{-- Featured carousel, mirroring Destination Template 1 --}}
    <div class="flat-recommended flat-section pb-0">
        <div class="container">
            <x-ui.section-title title="Explore our exclusive, otherworldly retreats"
                subtitle="Handpicked places that reward more than a weekend, with guides who live there." />

            <div class="flat-tab-recommend">
                <div class="swiper tf-swiper-tour wow animate__animated animate__fadeIn" data-gt-swiper="tour"
                    data-preview="3" data-tablet="2" data-mobile="1" data-space="30">
                    <div class="swiper-wrapper">
                        @foreach ($featured as $item)
                            <div class="swiper-slide">
                                <div class="item hover-img list-style-02">
                                    <div class="archive-top">
                                        <div class="images-group img-style">
                                            <a href="{{ route('destinations.show', $item) }}" class="image-link">
                                                <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" loading="lazy">
                                            </a>
                                            <div class="group-meta">
                                                <div class="tag-meta">
                                                    <div class="flag-tag">Featured</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="archive-bottom">
                                        <div class="content-top">
                                            <div class="address">
                                                <img src="{{ asset('assets/images/icons/place.svg') }}" alt="" class="icon">
                                                <span class="location">{{ $item->country }}</span>
                                            </div>
                                        </div>
                                        <div class="tour-title h3">
                                            <a href="{{ route('destinations.show', $item) }}" class="link multi-ellipsis">
                                                {{ $item->name }}
                                            </a>
                                        </div>
                                        <div class="content-bottom">
                                            <div class="content-info-middle">
                                                <div class="info date">
                                                    <img src="{{ asset('assets/images/icons/calendar.svg') }}" alt="" class="icon">
                                                    <span class="total-day">{{ $item->best_season }}</span>
                                                </div>
                                            </div>
                                            <div class="price h5">{{ $item->tours_count }} tours</div>
                                        </div>
                                    </div>
                                </div>
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

    {{-- Searchable grid of every destination --}}
    <div class="flat-section">
        <div class="container">
            <div class="gt-listing-toolbar">
                <div>
                    <div class="h3">All destinations</div>
                    <p class="subtitle text-color">
                        {{ $destinations->total() }} {{ Str::plural('place', $destinations->total()) }}
                        @if ($search) matching “{{ $search }}” @endif
                    </p>
                </div>

                <form method="GET" action="{{ route('destinations.index') }}"
                    class="d-flex gap-3 flex-wrap gt-inline-filter">
                    <input type="search" name="q" value="{{ $search }}" class="gt-select"
                        placeholder="Search destinations">
                    <select name="continent" class="gt-select" onchange="this.form.submit()">
                        <option value="">All continents</option>
                        @foreach ($continents as $item)
                            <option value="{{ $item }}" @selected($continent === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="tf-btn primary hover-1"><span>Search</span></button>
                </form>
            </div>

            @if ($destinations->isEmpty())
                <x-ui.empty-state title="No destinations match that search"
                    message="Try a different place name, or clear the filters to see all {{ \App\Models\Destination::active()->count() }} destinations.">
                    <a href="{{ route('destinations.index') }}" class="tf-btn primary hover-1"><span>Clear filters</span></a>
                </x-ui.empty-state>
            @else
                <div class="tf-destination gt-grid-3">
                    @foreach ($destinations as $destination)
                        <div class="wow animate__animated animate__fadeInUp">
                            <x-destination.card :destination="$destination" />
                        </div>
                    @endforeach
                </div>

                {{ $destinations->links() }}
            @endif
        </div>
    </div>

    {{-- Closing "top choice" strip from the template --}}
    <x-home.destinations :destinations="$topDestinations" />
</x-layouts.app>
