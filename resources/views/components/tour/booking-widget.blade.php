@props(['tour'])

@php
    $unit = (float) ($tour->sale_price ?? $tour->price);
    $times = ['07:00', '08:30', '09:00', '10:00', '13:30', '15:00'];
@endphp

{{--
    Live totals are computed client-side (initBookingWidget) purely for feedback;
    the authoritative figure is recalculated by BookingPricer on submit.
--}}
<div class="gt-booking" data-booking-widget data-price="{{ $unit }}"
    data-child-rate="{{ \App\Services\BookingPricer::CHILD_RATE }}" data-max-guests="{{ $tour->group_size }}">

    <div class="gt-booking__head">
        <div class="subtitle text-color">From</div>
        <div class="gt-booking__price">
            @if ($tour->is_on_sale)
                <span class="gt-price-was">${{ number_format($tour->price, 0) }}</span>
            @endif
            ${{ number_format($unit, 0) }}
            <span class="gt-price-suffix">/ person</span>
        </div>
    </div>

    <form action="{{ route('bookings.create', $tour) }}" method="GET" class="gt-booking__body">
        <div class="gt-field">
            <label for="gt-book-date">Choose date</label>
            <input type="date" id="gt-book-date" name="travel_date" required
                min="{{ now()->addDay()->toDateString() }}"
                value="{{ old('travel_date', now()->addDays(21)->toDateString()) }}">
        </div>

        <div class="gt-field">
            <label for="gt-book-time">Departure time</label>
            <select id="gt-book-time" name="travel_time">
                @foreach ($times as $time)
                    <option value="{{ $time }}">{{ $time }}</option>
                @endforeach
            </select>
        </div>

        <div class="gt-field">
            <label>Travellers</label>
            <div class="gt-stepper">
                <span>Adults</span>
                <div class="gt-stepper__controls">
                    <button type="button" data-step="-1" data-step-target="adults" aria-label="Fewer adults">−</button>
                    <input type="number" name="adults" data-count="adults" value="2" min="1"
                        max="{{ $tour->group_size }}" aria-label="Number of adults">
                    <button type="button" data-step="1" data-step-target="adults" aria-label="More adults">+</button>
                </div>
            </div>
            <div class="gt-stepper mt-2">
                <span>Children <small class="gt-hint">(60% rate)</small></span>
                <div class="gt-stepper__controls">
                    <button type="button" data-step="-1" data-step-target="children" aria-label="Fewer children">−</button>
                    <input type="number" name="children" data-count="children" value="0" min="0"
                        max="{{ $tour->group_size }}" aria-label="Number of children">
                    <button type="button" data-step="1" data-step-target="children" aria-label="More children">+</button>
                </div>
            </div>
            <p class="gt-warning" data-booking-warning hidden></p>
        </div>

        @if ($tour->extras)
            <div class="gt-field">
                <label>Add extra services</label>
                <div class="gt-extras">
                    @foreach ($tour->extras as $extra)
                        <label class="gt-extra">
                            <input type="checkbox" name="extras[]" value="{{ $extra['name'] }}"
                                data-extra="{{ $extra['name'] }}" data-extra-price="{{ $extra['price'] }}">
                            <span>{{ $extra['name'] }}</span>
                            <span class="gt-extra__price">+${{ number_format($extra['price']) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <ul class="gt-breakdown" data-booking-breakdown></ul>

        <div class="gt-booking__total">
            <span>Total</span>
            <span data-booking-total>${{ number_format($unit * 2, 0) }}</span>
        </div>

        <div class="gt-booking__cta">
            <button type="submit" class="tf-btn primary hover-1 w-full" data-booking-submit>
                <span>Book this tour</span>
            </button>
            <p class="gt-hint text-center mt-3">
                Free cancellation up to 30 days before departure. No payment taken today.
            </p>
        </div>
    </form>
</div>

<div class="gt-card mt-6">
    <div class="h5 mb-4">Tour information</div>
    <ul class="gt-stack">
        @php
            $info = [
                'Max guests' => $tour->group_size.' people',
                'Minimum age' => $tour->min_age > 0 ? $tour->min_age.' years' : 'All ages',
                'Difficulty' => ucfirst($tour->difficulty),
                'Location' => $tour->destination->full_name,
                'Languages' => implode(', ', $tour->languages ?? []),
                'Contact' => $tour->contact_phone,
            ];
        @endphp
        @foreach ($info as $label => $value)
            @continue(! $value)
            <li class="d-flex justify-content-between gap-3">
                <span class="subtitle text-color">{{ $label }}</span>
                <strong class="text-end">{{ $value }}</strong>
            </li>
        @endforeach
    </ul>
</div>
