@props(['tour'])

@php
    // Multi-day trips are numbered by day; day trips read as ordered stops.
    $isDayTrip = $tour->duration_days === 0;
@endphp

<div class="gt-itinerary" data-accordion data-accordion-multi>
    @foreach ($tour->itineraries as $step)
        <div class="gt-itinerary__item {{ $loop->first ? 'is-open' : '' }}" data-accordion-item>
            <span class="gt-itinerary__dot">{{ $step->day }}</span>

            <button type="button" class="gt-itinerary__trigger" data-accordion-trigger>
                <span>
                    <span class="gt-itinerary__day">
                        {{ $isDayTrip ? 'Stop '.$step->day : 'Day '.$step->day }}
                        @if ($step->duration) · {{ $step->duration }} @endif
                    </span>
                    <span class="gt-itinerary__title d-block">{{ $step->title }}</span>
                </span>
                <i class="icon icon-CaretDown gt-itinerary__caret"></i>
            </button>

            <div class="gt-itinerary__body">
                <p>{{ $step->description }}</p>

                @if ($step->meals || $step->accommodation)
                    <div class="gt-itinerary__meta">
                        @if ($step->meals)
                            <x-ui.badge tone="muted">Meals: {{ $step->meals }}</x-ui.badge>
                        @endif
                        @if ($step->accommodation)
                            <x-ui.badge tone="muted">Stay: {{ $step->accommodation }}</x-ui.badge>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
