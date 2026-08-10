<x-layouts.admin :title="$user->name">

    <x-admin.page-header :title="$user->name" :subtitle="$user->email">
        <a href="{{ route('admin.users.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
        <a href="{{ route('admin.users.edit', $user) }}" class="adm-btn">Edit user</a>
    </x-admin.page-header>

    <div class="adm-grid-2">
        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Recent bookings</div></div>
            @if ($bookings->isEmpty())
                <div class="adm-empty"><p>This account has not booked anything yet.</p></div>
            @else
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr><th>Reference</th><th>Tour</th><th>Departure</th><th>Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="adm-table__title">
                                            {{ $booking->reference }}
                                        </a>
                                    </td>
                                    <td><span class="adm-clamp-2">{{ $booking->tour->title }}</span></td>
                                    <td>{{ $booking->travel_date->format('j M Y') }}</td>
                                    <td>${{ number_format($booking->total, 2) }}</td>
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
            @endif
        </div>

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Account</div></div>
            <div class="adm-panel__body">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px">
                    <img src="{{ $user->avatar_url }}" alt=""
                        style="width:64px;height:64px;border-radius:50%;object-fit:cover">
                    <div>
                        <div style="font-weight:700;font-size:17px">{{ $user->name }}</div>
                        <x-ui.badge :tone="$user->role->badge()">{{ $user->role->label() }}</x-ui.badge>
                    </div>
                </div>

                <div class="adm-stack">
                    <div class="adm-kv">
                        <span class="adm-kv__key">Email</span>
                        <span class="adm-kv__val">{{ $user->email }}</span>
                    </div>
                    <div class="adm-kv">
                        <span class="adm-kv__key">Phone</span>
                        <span class="adm-kv__val">{{ $user->phone ?? '—' }}</span>
                    </div>
                    <div class="adm-kv">
                        <span class="adm-kv__key">Country</span>
                        <span class="adm-kv__val">{{ $user->country ?? '—' }}</span>
                    </div>
                    <div class="adm-kv">
                        <span class="adm-kv__key">Status</span>
                        <span class="adm-kv__val">
                            <x-ui.badge :tone="$user->status === 'active' ? 'success' : 'danger'">
                                {{ ucfirst($user->status) }}
                            </x-ui.badge>
                        </span>
                    </div>
                    <div class="adm-kv">
                        <span class="adm-kv__key">Bookings</span>
                        <span class="adm-kv__val">{{ $user->bookings_count }}</span>
                    </div>
                    <div class="adm-kv">
                        <span class="adm-kv__key">Reviews</span>
                        <span class="adm-kv__val">{{ $user->reviews_count }}</span>
                    </div>
                    <div class="adm-kv">
                        <span class="adm-kv__key">Joined</span>
                        <span class="adm-kv__val">{{ $user->created_at->format('j F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
