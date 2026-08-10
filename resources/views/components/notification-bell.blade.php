{{--
    Shared by the admin topbar and the site header. Styles live in globetrek.css,
    which both layouts load, so the bell looks the same on either side.
--}}
@auth
    @php
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()->limit(8)->get();
    @endphp

    <div class="gt-notif" data-notif>
        <button type="button" class="gt-notif__trigger" data-notif-toggle aria-expanded="false"
            aria-label="Notifications{{ $unreadCount ? " ({$unreadCount} unread)" : '' }}">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                <path d="M12 3a6 6 0 0 0-6 6v3.6l-1.3 2.5a1 1 0 0 0 .9 1.5h12.8a1 1 0 0 0 .9-1.5L18 12.6V9a6 6 0 0 0-6-6Z"
                    fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                <path d="M9.5 19a2.5 2.5 0 0 0 5 0" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" />
            </svg>
            @if ($unreadCount)
                <span class="gt-notif__count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </button>

        <div class="gt-notif__panel" data-notif-panel hidden>
            <div class="gt-notif__head">
                <span>Notifications</span>
                @if ($unreadCount)
                    <form method="POST" action="{{ route('notifications.readAll') }}">
                        @csrf
                        <button type="submit" class="gt-notif__readall">Mark all read</button>
                    </form>
                @endif
            </div>

            <ul class="gt-notif__list">
                @forelse ($recent as $item)
                    <li @class(['gt-notif__item', 'is-unread' => ! $item->read_at])>
                        <form method="POST" action="{{ route('notifications.read', $item->id) }}">
                            @csrf
                            <button type="submit">
                                <span class="gt-notif__title">{{ $item->data['title'] ?? 'Notification' }}</span>
                                <span class="gt-notif__msg">{{ $item->data['message'] ?? '' }}</span>
                                <span class="gt-notif__foot">
                                    {{ $item->created_at->diffForHumans() }}@if (! empty($item->data['meta'])) · {{ $item->data['meta'] }}@endif
                                </span>
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="gt-notif__empty">Nothing yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- The two layouts load different bundles, so the bell carries its own script. --}}
    @once
        <script>
            (function () {
                const close = (root) => {
                    root.querySelector('[data-notif-panel]').setAttribute('hidden', '');
                    root.querySelector('[data-notif-toggle]').setAttribute('aria-expanded', 'false');
                };

                document.addEventListener('click', (event) => {
                    const toggle = event.target.closest('[data-notif-toggle]');

                    document.querySelectorAll('[data-notif]').forEach((root) => {
                        if (toggle && root.contains(toggle)) {
                            const panel = root.querySelector('[data-notif-panel]');
                            const opening = panel.hasAttribute('hidden');
                            panel.toggleAttribute('hidden', !opening);
                            root.querySelector('[data-notif-toggle]').setAttribute('aria-expanded', String(opening));
                        } else if (!root.contains(event.target)) {
                            close(root);
                        }
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') document.querySelectorAll('[data-notif]').forEach(close);
                });
            })();
        </script>
    @endonce
@endauth
