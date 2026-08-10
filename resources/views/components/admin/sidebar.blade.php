@php
    // Homepage and Users are admin-only areas, so managers are not shown a door
    // that gives them a 403.
    $isAdmin = auth()->user()?->isAdmin() ?? false;

    $nav = array_filter([
        'Overview' => [
            ['admin.dashboard', 'Dashboard', 'icon-Category', null],
        ],
        'Content' => $isAdmin ? [
            ['admin.pages.index', 'Pages', 'icon-Home', null],
        ] : [],
        'Catalogue' => [
            ['admin.tours.index', 'Tours', 'icon-MapPin', \App\Models\Tour::count()],
            ['admin.destinations.index', 'Destinations', 'icon-Home', \App\Models\Destination::count()],
            ['admin.categories.index', 'Categories', 'icon-list', \App\Models\Category::count()],
        ],
        'Operations' => array_filter([
            ['admin.bookings.index', 'Bookings', 'icon-history', \App\Models\Booking::where('status', 'pending')->count() ?: null],
            ['admin.reviews.index', 'Reviews', 'icon-star', \App\Models\Review::where('status', 'pending')->count() ?: null],
            $isAdmin ? ['admin.users.index', 'Users', 'icon-Profile', \App\Models\User::count()] : null,
        ]),
    ]);
@endphp

<aside class="adm-sidebar" id="adm-sidebar">
    <div class="adm-sidebar__brand">
        <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/images/logo/logo.svg') }}" alt="{{ config('app.name') }}">
        </a>
        <button type="button" class="adm-sidebar__close" data-sidebar-close aria-label="Close menu">
            <i class="icon icon-X"></i>
        </button>
    </div>

    <nav class="adm-nav">
        @foreach ($nav as $group => $items)
            <div class="adm-nav__group">
                <div class="adm-nav__label">{{ $group }}</div>
                @foreach ($items as [$route, $label, $icon, $count])
                    <a href="{{ route($route) }}"
                        class="adm-nav__link {{ request()->routeIs(Str::beforeLast($route, '.').'.*') ? 'is-active' : '' }}">
                        <i class="icon {{ $icon }}"></i>
                        <span>{{ $label }}</span>
                        @if ($count)
                            <span class="adm-nav__count">{{ $count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="adm-sidebar__foot">
        <a href="{{ route('home') }}" class="adm-nav__link" target="_blank" rel="noopener">
            <i class="icon icon-arrow-right"></i>
            <span>View website</span>
        </a>
    </div>
</aside>
