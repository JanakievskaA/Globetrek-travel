<x-layouts.admin title="Dashboard">

    <x-admin.page-header title="Overview"
        :subtitle="'Everything happening across the platform, as of '.now()->format('j M Y, H:i').'.'">
        <a href="{{ route('admin.tours.create') }}" class="adm-btn">+ New tour</a>
    </x-admin.page-header>

    <div class="adm-stats">
        <x-admin.stat-card label="Revenue this month" value="${{ number_format($stats['revenue'], 2) }}"
            :delta="$stats['revenueDelta']" icon="icon-range" tone="ok" />
        <x-admin.stat-card label="Total bookings" value="{{ number_format($stats['bookings']) }}"
            :hint="$stats['pendingBookings'].' awaiting confirmation'" icon="icon-history" tone="info" />
        <x-admin.stat-card label="Published tours"
            value="{{ $stats['publishedTours'] }} / {{ $stats['tours'] }}"
            :hint="$stats['destinations'].' destinations'" icon="icon-MapPin" tone="brand" />
        <x-admin.stat-card label="Reviews pending" value="{{ $stats['pendingReviews'] }}"
            :hint="'Average score '.$stats['avgRating'].' / 5'" icon="icon-star"
            :tone="$stats['pendingReviews'] > 0 ? 'warn' : 'ok'" />
    </div>

    <div class="adm-grid-2">
        <div>
            {{-- Revenue chart, rendered as scaled bars — no charting library needed --}}
            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Revenue, last 12 months</div>
                    <span class="adm-hint">Confirmed and completed bookings</span>
                </div>
                <div class="adm-panel__body">
                    @php $peak = max(1, collect($revenueByMonth)->max('value')); @endphp
                    <div class="adm-chart">
                        @foreach ($revenueByMonth as $month)
                            <div class="adm-chart__col" title="{{ $month['label'] }}: ${{ number_format($month['value'], 2) }}">
                                <span class="adm-chart__value">
                                    {{ $month['value'] > 0 ? '$'.round($month['value'] / 1000, 1).'k' : '—' }}
                                </span>
                                <div class="adm-chart__bar"
                                    style="height: {{ max(2, round(($month['value'] / $peak) * 100)) }}%"></div>
                                <span class="adm-chart__label">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Recent bookings</div>
                    <a href="{{ route('admin.bookings.index') }}" class="adm-btn adm-btn--ghost">View all</a>
                </div>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Tour</th>
                                <th>Travel date</th>
                                <th style="text-align:right">Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentBookings as $booking)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="adm-table__title">
                                            {{ $booking->reference }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $booking->customer_name }}
                                        <div class="adm-table__sub">{{ $booking->customer_email }}</div>
                                    </td>
                                    <td><span class="adm-clamp-2">{{ $booking->tour->title }}</span></td>
                                    <td>{{ $booking->travel_date->format('j M Y') }}</td>
                                    <td style="text-align:right">${{ number_format($booking->total, 2) }}</td>
                                    <td>
                                        <x-ui.badge :tone="$booking->status->badge()">
                                            {{ $booking->status->label() }}
                                        </x-ui.badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Most booked tours</div>
                </div>
                <div class="adm-panel__body">
                    <div class="adm-list">
                        @foreach ($topTours as $index => $tour)
                            <div class="adm-list__row">
                                <span class="adm-list__rank">{{ $index + 1 }}</span>
                                <div class="adm-list__main">
                                    <div class="adm-list__title">{{ $tour->title }}</div>
                                    <div class="adm-list__sub">
                                        {{ number_format($tour->rating_avg, 1) }}★ · ${{ number_format($tour->price) }}
                                    </div>
                                </div>
                                <span class="adm-list__value">{{ $tour->bookings_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Reviews to moderate</div>
                    <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}"
                        class="adm-btn adm-btn--ghost">Queue</a>
                </div>
                <div class="adm-panel__body">
                    @if ($pendingReviewList->isEmpty())
                        <p class="adm-hint">Nothing waiting — the queue is clear.</p>
                    @else
                        <div class="adm-list">
                            @foreach ($pendingReviewList as $review)
                                <div class="adm-list__row">
                                    <div class="adm-list__main">
                                        <div class="adm-list__title">{{ $review->author_name }}</div>
                                        <div class="adm-list__sub">{{ Str::limit($review->tour->title, 34) }}</div>
                                    </div>
                                    <span class="adm-stars">
                                        @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? '' : 'is-off' }}">★</span>@endfor
                                    </span>
                                    <form action="{{ route('admin.reviews.status', $review) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="adm-icon-btn" title="Approve">
                                            <i class="icon icon-check"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Destinations by catalogue size</div>
                </div>
                <div class="adm-panel__body">
                    <div class="adm-list">
                        @foreach ($topDestinations as $index => $destination)
                            <div class="adm-list__row">
                                <span class="adm-list__rank">{{ $index + 1 }}</span>
                                <div class="adm-list__main">
                                    <div class="adm-list__title">{{ $destination->name }}</div>
                                    <div class="adm-list__sub">{{ $destination->country }}</div>
                                </div>
                                <span class="adm-list__value">{{ $destination->tours_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
