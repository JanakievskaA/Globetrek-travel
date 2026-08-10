<x-layouts.app title="My bookings">

    <x-ui.page-title title="My bookings" :breadcrumbs="['My bookings' => null]" />

    <div class="flat-section">
        <div class="container">
            @if ($bookings->isEmpty())
                <x-ui.empty-state title="You have no bookings yet"
                    message="Once you book a tour it will appear here with its reference and status.">
                    <a href="{{ route('tours.index') }}" class="tf-btn primary hover-1"><span>Browse tours</span></a>
                </x-ui.empty-state>
            @else
                <div class="gt-stack">
                    @foreach ($bookings as $booking)
                        <div class="gt-card d-flex gap-4 flex-wrap align-items-center">
                            <img src="{{ asset($booking->tour->image) }}" alt="{{ $booking->tour->title }}"
                                style="width:130px;height:96px;object-fit:cover;border-radius:12px">

                            <div class="flex-1" style="min-width:220px">
                                <div class="h5">
                                    <a href="{{ route('tours.show', $booking->tour) }}">{{ $booking->tour->title }}</a>
                                </div>
                                <p class="gt-hint mt-1">
                                    {{ $booking->tour->destination->full_name }} ·
                                    {{ $booking->travel_date->format('j M Y') }} ·
                                    {{ $booking->travellers }} {{ Str::plural('traveller', $booking->travellers) }}
                                </p>
                                <p class="gt-hint">Reference {{ $booking->reference }}</p>
                            </div>

                            <div class="text-end">
                                <x-ui.badge :tone="$booking->status->badge()">{{ $booking->status->label() }}</x-ui.badge>
                                <div class="h5 mt-2">${{ number_format($booking->total, 2) }}</div>
                                <a href="{{ route('bookings.show', $booking) }}" class="gt-hint text_primary">
                                    View details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{ $bookings->links() }}
            @endif
        </div>
    </div>
</x-layouts.app>
