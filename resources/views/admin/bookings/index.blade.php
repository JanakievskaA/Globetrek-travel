<x-layouts.admin title="Bookings">

    <x-admin.page-header title="Bookings" :subtitle="number_format($bookings->total()).' matching bookings'" />

    <div class="adm-stats">
        <x-admin.stat-card label="All bookings" value="{{ number_format($totals['all']) }}" icon="icon-history" tone="info" />
        <x-admin.stat-card label="Awaiting confirmation" value="{{ $totals['pending'] }}" icon="icon-clock"
            :tone="$totals['pending'] > 0 ? 'warn' : 'ok'" />
        <x-admin.stat-card label="Upcoming departures" value="{{ $totals['upcoming'] }}" icon="icon-MapPin" tone="brand" />
        <x-admin.stat-card label="Lifetime revenue" value="${{ number_format($totals['revenue'], 2) }}"
            icon="icon-range" tone="ok" />
    </div>

    <x-admin.data-table :paginator="$bookings" empty="No bookings match these filters."
        :headers="['Reference', 'Customer', 'Tour', 'Departure', 'Guests', 'Total', 'Payment', 'Status', ['label' => 'Actions', 'align' => 'right']]">

        <x-slot:filters>
            <form method="GET" action="{{ route('admin.bookings.index') }}" data-auto-filter class="adm-filters"
                style="border:0;padding:0;background:none;width:100%">
                <input type="search" name="q" value="{{ request('q') }}"
                    placeholder="Reference, name, email or tour…" class="adm-grow">
                <select name="status">
                    <option value="">Any status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="payment">
                    <option value="">Any payment</option>
                    @foreach (['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('payment') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" title="Departing from">
                <input type="date" name="to" value="{{ request('to') }}" title="Departing until">
                @if (request()->hasAny(['q', 'status', 'payment', 'from', 'to']))
                    <a href="{{ route('admin.bookings.index') }}" class="adm-btn adm-btn--ghost">Reset</a>
                @endif
            </form>
        </x-slot:filters>

        @foreach ($bookings as $booking)
            <tr>
                <td>
                    <a href="{{ route('admin.bookings.show', $booking) }}" class="adm-table__title">
                        {{ $booking->reference }}
                    </a>
                    <div class="adm-table__sub">{{ $booking->created_at->format('j M Y') }}</div>
                </td>
                <td>
                    {{ $booking->customer_name }}
                    <div class="adm-table__sub">{{ $booking->customer_email }}</div>
                </td>
                <td>
                    <span class="adm-clamp-2">{{ $booking->tour->title }}</span>
                    <div class="adm-table__sub">{{ $booking->tour->destination->name }}</div>
                </td>
                <td>
                    {{ $booking->travel_date->format('j M Y') }}
                    @if ($booking->travel_time)
                        <div class="adm-table__sub">{{ $booking->travel_time }}</div>
                    @endif
                </td>
                <td>{{ $booking->adults }}A{{ $booking->children ? ' · '.$booking->children.'C' : '' }}</td>
                <td><strong>${{ number_format($booking->total, 2) }}</strong></td>
                <td>
                    <x-ui.badge :tone="match ($booking->payment_status) {
                        'paid' => 'success', 'refunded' => 'danger', default => 'warning' }">
                        {{ ucfirst($booking->payment_status) }}
                    </x-ui.badge>
                </td>
                <td>
                    {{-- Inline status change, posted on select --}}
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="adm-inline-form">
                        @csrf @method('PATCH')
                        <select name="status" aria-label="Change status">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($booking->status->value === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </td>
                <td>
                    <x-admin.row-actions :edit="route('admin.bookings.edit', $booking)"
                        :destroy="route('admin.bookings.destroy', $booking)"
                        :confirm="'Delete booking '.$booking->reference.'?'">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="adm-icon-btn" title="Details">
                            <i class="icon icon-Search"></i>
                        </a>
                    </x-admin.row-actions>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
