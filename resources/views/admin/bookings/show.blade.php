<x-layouts.admin :title="'Booking '.$booking->reference">

    <x-admin.page-header :title="$booking->reference"
        :subtitle="'Created '.$booking->created_at->format('j F Y, H:i')">
        <a href="{{ route('admin.bookings.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
        <a href="{{ route('admin.bookings.edit', $booking) }}" class="adm-btn">Edit booking</a>
    </x-admin.page-header>

    <div class="adm-grid-2">
        <div>
            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Trip</div>
                    <x-ui.badge :tone="$booking->status->badge()">{{ $booking->status->label() }}</x-ui.badge>
                </div>
                <div class="adm-panel__body">
                    <div class="adm-table__media mb-4" style="margin-bottom:18px">
                        <img src="{{ asset($booking->tour->image) }}" alt="" class="adm-table__thumb"
                            style="width:96px;height:70px;flex-basis:96px">
                        <div>
                            <a href="{{ route('admin.tours.edit', $booking->tour) }}" class="adm-table__title">
                                {{ $booking->tour->title }}
                            </a>
                            <div class="adm-table__sub">{{ $booking->tour->destination->full_name }}</div>
                        </div>
                    </div>

                    <div class="adm-stack">
                        <div class="adm-kv">
                            <span class="adm-kv__key">Departure date</span>
                            <span class="adm-kv__val">{{ $booking->travel_date->format('l j F Y') }}</span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Departure time</span>
                            <span class="adm-kv__val">{{ $booking->travel_time ?? '—' }}</span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Duration</span>
                            <span class="adm-kv__val">{{ $booking->tour->duration_label }}</span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Guests</span>
                            <span class="adm-kv__val">
                                {{ $booking->adults }} adults{{ $booking->children ? ', '.$booking->children.' children' : '' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($booking->notes)
                <div class="adm-panel">
                    <div class="adm-panel__head"><div class="adm-panel__title">Customer notes</div></div>
                    <div class="adm-panel__body"><p>{{ $booking->notes }}</p></div>
                </div>
            @endif
        </div>

        <div>
            <div class="adm-panel">
                <div class="adm-panel__head"><div class="adm-panel__title">Customer</div></div>
                <div class="adm-panel__body">
                    <div class="adm-stack">
                        <div class="adm-kv">
                            <span class="adm-kv__key">Name</span>
                            <span class="adm-kv__val">{{ $booking->customer_name }}</span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Email</span>
                            <span class="adm-kv__val">
                                <a href="mailto:{{ $booking->customer_email }}">{{ $booking->customer_email }}</a>
                            </span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Phone</span>
                            <span class="adm-kv__val">{{ $booking->customer_phone ?? '—' }}</span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Country</span>
                            <span class="adm-kv__val">{{ $booking->customer_country ?? '—' }}</span>
                        </div>
                        @if ($booking->user)
                            <div class="adm-kv">
                                <span class="adm-kv__key">Account</span>
                                <span class="adm-kv__val">
                                    <a href="{{ route('admin.users.show', $booking->user) }}">Registered user</a>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="adm-panel">
                <div class="adm-panel__head"><div class="adm-panel__title">Charges</div></div>
                <div class="adm-panel__body">
                    <div class="adm-stack">
                        <div class="adm-kv">
                            <span class="adm-kv__key">Tour subtotal</span>
                            <span class="adm-kv__val">${{ number_format($booking->subtotal, 2) }}</span>
                        </div>
                        @foreach ($booking->extras ?? [] as $extra)
                            <div class="adm-kv">
                                <span class="adm-kv__key">{{ $extra['name'] }}</span>
                                <span class="adm-kv__val">${{ number_format($extra['price'], 2) }}</span>
                            </div>
                        @endforeach
                        <div class="adm-kv">
                            <span class="adm-kv__key"><strong>Total</strong></span>
                            <span class="adm-kv__val" style="font-size:18px">
                                ${{ number_format($booking->total, 2) }}
                            </span>
                        </div>
                        <div class="adm-kv">
                            <span class="adm-kv__key">Payment</span>
                            <span class="adm-kv__val">
                                <x-ui.badge :tone="match ($booking->payment_status) {
                                    'paid' => 'success', 'refunded' => 'danger', default => 'warning' }">
                                    {{ ucfirst($booking->payment_status) }}
                                </x-ui.badge>
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('admin.bookings.status', $booking) }}" method="POST"
                        style="margin-top:18px;display:flex;gap:8px">
                        @csrf @method('PATCH')
                        <select name="status" class="adm-grow"
                            style="border:1px solid var(--adm-line);border-radius:9px;padding:8px 12px">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($booking->status->value === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="adm-btn">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
