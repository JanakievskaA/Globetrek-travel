<x-layouts.app :title="'Booking '.$booking->reference">

    <x-ui.page-title title="Booking confirmed" :breadcrumbs="['Booking' => null]" />

    <div class="flat-section">
        <div class="container">
            <div class="gt-card text-center" style="max-width:720px;margin:0 auto">
                <img src="{{ asset('assets/images/logo/finish.png') }}" alt="" style="width:96px">
                <div class="h2 mt-4">Thank you, {{ Str::before($booking->customer_name, ' ') }}</div>
                <p class="text-color mt-3">
                    Your request for <strong>{{ $booking->tour->title }}</strong> is with our team. We confirm
                    availability within one working day and email you at
                    <strong>{{ $booking->customer_email }}</strong>.
                </p>

                <div class="gt-info-grid mt-8 text-start">
                    @php
                        $rows = [
                            ['trip.svg', 'Reference', $booking->reference],
                            ['calendar.svg', 'Departure', $booking->travel_date->format('j F Y').($booking->travel_time ? ' · '.$booking->travel_time : '')],
                            ['users.svg', 'Travellers', $booking->adults.' adults'.($booking->children ? ', '.$booking->children.' children' : '')],
                            ['place.svg', 'Destination', $booking->tour->destination->full_name],
                            ['clock.svg', 'Duration', $booking->tour->duration_label],
                            ['money.svg', 'Total', '$'.number_format($booking->total, 2)],
                        ];
                    @endphp
                    @foreach ($rows as [$icon, $label, $value])
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

                <div class="mt-6 d-flex gap-2 justify-content-center align-items-center">
                    <span class="subtitle text-color">Status</span>
                    <x-ui.badge :tone="$booking->status->badge()">{{ $booking->status->label() }}</x-ui.badge>
                </div>

                @if ($booking->extras)
                    <div class="mt-6 text-start">
                        <div class="h5 mb-3">Extras added</div>
                        <div class="gt-tags">
                            @foreach ($booking->extras as $extra)
                                <span class="gt-tag">{{ $extra['name'] }} · ${{ number_format($extra['price']) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="d-flex gap-3 justify-content-center mt-8 flex-wrap">
                    <a href="{{ route('tours.show', $booking->tour) }}" class="tf-btn hover-1"><span>View tour</span></a>
                    <a href="{{ route('tours.index') }}" class="tf-btn primary hover-1"><span>Browse more tours</span></a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
