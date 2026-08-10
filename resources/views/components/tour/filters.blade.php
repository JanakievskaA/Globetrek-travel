@props([
    'filters',
    'categories',
    'destinations',
    'durations',
    'amenities',
    'priceBounds',
    'total' => 0,
])

{{--
    One GET form drives the whole sidebar. Checkboxes and selects submit on
    change (see initFilterForm in globetrek.js); the search box debounces.
    Sort and layout ride along as hidden inputs so they survive filtering.
--}}
<form method="GET" action="{{ route('tours.index') }}" id="gt-filter-form" class="sidebar-filter sticky-sidebar">
    <input type="hidden" name="sort" id="gt-filter-sort" value="{{ $filters->sort->value }}">
    <input type="hidden" name="layout" id="gt-filter-layout" value="{{ $filters->layout }}">

    <div class="gt-filter-head">
        <div>
            <div class="title-filter h4">Filter</div>
            <span class="subtitle text_primary">{{ $total }} {{ Str::plural('tour', $total) }} found</span>
        </div>
        @if ($filters->hasAny())
            <a href="{{ route('tours.index') }}" class="gt-filter-reset">Reset all</a>
        @endif
    </div>

    <div class="widget-item">
        <label class="text-black text-capitalize h5" for="gt-filter-search">Search</label>
        <div class="search-box mt-3">
            <input type="search" name="q" id="gt-filter-search" class="gt-select w-full"
                value="{{ $filters->search }}" placeholder="Tour, city or country" autocomplete="off">
        </div>
    </div>

    <div class="widget-item">
        <label class="text-black text-capitalize h5" for="gt-filter-destination">Destination</label>
        <select name="destination" id="gt-filter-destination" class="gt-select w-full mt-3">
            <option value="">Anywhere</option>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->slug }}" @selected($filters->destination === $destination->slug)>
                    {{ $destination->full_name }} ({{ $destination->tours_count }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="widget-item widget-type-tour">
        <div class="text-black text-capitalize h5">Tour type</div>
        <div class="gt-filter-list mt-3">
            @foreach ($categories as $category)
                <label class="gt-check">
                    <input type="checkbox" name="category[]" value="{{ $category->slug }}"
                        @checked($filters->isChecked('category', $category->slug))>
                    <span>{{ $category->name }}</span>
                    <span class="gt-check__count">{{ $category->tours_count }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="widget-item">
        <label class="text-black text-capitalize h5" for="gt-filter-date">Departure date</label>
        <input type="date" name="date" id="gt-filter-date" class="gt-select w-full mt-3"
            value="{{ $filters->date }}" min="{{ now()->toDateString() }}">
    </div>

    <div class="widget-item">
        <label class="text-black text-capitalize h5" for="gt-filter-people">Travellers</label>
        <select name="people" id="gt-filter-people" class="gt-select w-full mt-3" data-no-filter>
            <option value="">Any group size</option>
            @for ($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" @selected($filters->people === $i)>
                    {{ $i }}+ {{ Str::plural('traveller', $i) }}
                </option>
            @endfor
        </select>
    </div>

    <div class="widget-item widget-advanced-price">
        <div class="box-title-price d-flex justify-content-between align-items-center">
            <span class="text-black text-capitalize h5">Price</span>
            <span class="gt-price-label" id="gt-price-label">
                ${{ number_format($filters->minPrice) }} – ${{ number_format($filters->maxPrice) }}{{ $filters->maxPrice >= $priceBounds[1] ? '+' : '' }}
            </span>
        </div>
        <div id="gt-price-slider" data-floor="{{ $priceBounds[0] }}" data-ceiling="{{ $priceBounds[1] }}"></div>
        <input type="hidden" name="min_price" id="gt-price-min" value="{{ $filters->minPrice }}">
        <input type="hidden" name="max_price" id="gt-price-max" value="{{ $filters->maxPrice }}">
    </div>

    <div class="widget-item widget-duration-tour">
        <div class="text-black text-capitalize h5">Duration</div>
        <div class="gt-filter-list mt-3">
            @foreach ($durations as $duration)
                <label class="gt-check">
                    <input type="checkbox" name="duration[]" value="{{ $duration->value }}"
                        @checked($filters->isChecked('duration', $duration->value))>
                    <span>{{ $duration->label() }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="widget-item widget-rate">
        <div class="text-black text-capitalize h5">Review score</div>
        {{-- Averages rarely reach a clean 5.0, so thresholds are half-star
             bands (the convention on the big booking sites). --}}
        <div class="gt-filter-list mt-3">
            @foreach (['4.5' => 'Exceptional', '4' => 'Excellent', '3.5' => 'Very good', '3' => 'Good'] as $value => $label)
                <label class="gt-check">
                    <input type="checkbox" name="rating[]" value="{{ $value }}"
                        @checked($filters->isChecked('rating', $value))>
                    <span class="rating">
                        <span class="list-star">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="icon icon-star {{ $i <= ceil((float) $value) ? '' : 'gt-star-empty' }}"></span>
                            @endfor
                        </span>
                    </span>
                    <span class="gt-check__count">{{ $value }}+ {{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="widget-item widget-amenities-tour">
        <div class="text-black text-capitalize h5">Included</div>
        <div class="gt-filter-list mt-3">
            @foreach ($amenities as $amenity)
                <label class="gt-check">
                    <input type="checkbox" name="amenity[]" value="{{ $amenity }}"
                        @checked($filters->isChecked('amenity', $amenity))>
                    <span>{{ $amenity }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Progressive enhancement: without JS the form still needs a submit. --}}
    <noscript>
        <button type="submit" class="tf-btn primary hover-1 w-full mt-4"><span>Apply filters</span></button>
    </noscript>
</form>
