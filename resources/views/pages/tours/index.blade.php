@php
    $categoryNames = $categories->pluck('name', 'slug')->all();
    $destinationNames = $destinations->pluck('full_name', 'slug')->all();
    $chips = $filters->activeChips($categoryNames, $destinationNames);

    // Describe what is being listed, so the count only has to be stated once.
    $heading = match (true) {
        $filters->search !== null => 'Results for “'.$filters->search.'”',
        $filters->destination !== null => 'Tours in '.($destinationNames[$filters->destination] ?? 'this destination'),
        count($filters->categories) === 1 => ($categoryNames[$filters->categories[0]] ?? 'Filtered').' tours',
        $filters->hasAny() => 'Filtered tours',
        default => 'All tours',
    };
@endphp

<x-layouts.app title="All tours"
    description="Filter {{ $tours->total() }} small-group tours by destination, tour type, price, duration and traveller rating.">

    <x-ui.page-title title="Listing tours" :breadcrumbs="['Tours' => null]" />

    {{-- No `pt-0` (the theme marks it !important, collapsing the gap); the
         explicit top gap keeps the spacing consistent below 1440px too. --}}
    <div class="flat-section flat-recommended gt-section-top-gap">
        <div class="container">
            <div class="flat-tab-recommend">
                <div class="row">
                    {{-- Sidebar --}}
                    <div class="col-xl-3 col-md-12">
                        <div class="wow animate__animated animate__fadeInDown">
                            <x-tour.filters :filters="$filters" :categories="$categories" :destinations="$destinations"
                                :durations="$durations" :amenities="$amenities" :price-bounds="$priceBounds"
                                :total="$tours->total()" />
                        </div>
                    </div>

                    {{-- Results --}}
                    <div class="col-xl-9 col-md-12">
                        <div class="gt-listing-toolbar">
                            <div>
                                <div class="h4">{{ $heading }}</div>
                                <p class="subtitle text-color">
                                    @if ($tours->total() === 0)
                                        No matches
                                    @elseif ($tours->hasPages())
                                        Showing {{ $tours->firstItem() }}–{{ $tours->lastItem() }}
                                        of {{ $tours->total() }} {{ Str::plural('tour', $tours->total()) }}
                                    @else
                                        {{ $tours->total() }} {{ Str::plural('tour', $tours->total()) }}
                                    @endif
                                </p>
                            </div>

                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="gt-sort">
                                    <label for="gt-sort-select" class="subtitle text-color">Sort</label>
                                    <select id="gt-sort-select">
                                        @foreach ($sortOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($filters->sort->value === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="gt-layout-switch" role="group" aria-label="Result layout">
                                    <button type="button" data-layout-switch="list"
                                        class="{{ $filters->layout === 'list' ? 'is-active' : '' }}" aria-label="List view">
                                        <img src="{{ asset('assets/images/icons/list.svg') }}" alt="" class="icon">
                                    </button>
                                    <button type="button" data-layout-switch="grid"
                                        class="{{ $filters->layout === 'grid' ? 'is-active' : '' }}" aria-label="Grid view">
                                        <img src="{{ asset('assets/images/icons/list-2.svg') }}" alt="" class="icon">
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if ($chips)
                            <div class="gt-chips">
                                @foreach ($chips as $chip)
                                    <a class="gt-chip"
                                        href="{{ route('tours.index', $filters->without($chip['key'], $chip['value'])) }}">
                                        {{ $chip['label'] }}
                                        <span class="gt-chip__x" aria-hidden="true">&times;</span>
                                    </a>
                                @endforeach
                                <a class="gt-chip" href="{{ route('tours.index') }}"><strong>Clear all</strong></a>
                            </div>
                        @endif

                        @if ($tours->isEmpty())
                            <x-ui.empty-state title="No tours match those filters"
                                message="Try widening the price range, removing a tour type, or searching a different destination.">
                                <a href="{{ route('tours.index') }}" class="tf-btn primary hover-1">
                                    <span>Clear all filters</span>
                                </a>
                            </x-ui.empty-state>
                        @else
                            <div class="tf-grid-layout wow animate__animated animate__fadeIn
                                {{ $filters->layout === 'grid' ? 'tf-col-3 lg-col-2 sm-col-1' : 'tf-col-1 wrap-list' }}">
                                @foreach ($tours as $tour)
                                    <x-tour.card :tour="$tour"
                                        :variant="$filters->layout === 'grid' ? 'grid' : 'list'" />
                                @endforeach
                            </div>

                            {{ $tours->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
