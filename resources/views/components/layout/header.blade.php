@props(['variant' => 'solid'])

@php
    // The homepage hero sits behind a transparent header; every other page
    // uses the solid variant so the dark logo and links stay readable.
    // $navDestinations / $navCategories are supplied by NavigationComposer.
    $overlay = $variant === 'overlay';
    $logo = $overlay ? 'logo-white.svg' : 'logo.svg';
@endphp

<header id="header"
    class="header position-fixed {{ $overlay ? 'style-2 color-2' : 'style-1 color-1' }}">
    <div class="{{ $overlay ? 'container-2' : 'container' }}">
        <div class="header-wrap relative w-full">
            <div class="header-ct-left flex-left z-5">
                <div class="logo flex-center" id="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/logo/'.$logo) }}" alt="{{ config('app.name') }}">
                    </a>
                </div>
            </div>

            <div class="header-ct-center flex-center z-5">
                <div class="inner-center">
                    <div class="nav-wrap">
                        <nav class="list-nav">
                            <ul class="menu-nav" id="menu-nav">
                                <li class="menu-item flex-center gap-1 h-full {{ request()->routeIs('home') ? 'current-menu' : '' }}">
                                    <a href="{{ route('home') }}" class="nav-link">Home</a>
                                </li>

                                <li class="menu-item flex-center gap-1 h-full {{ request()->routeIs('tours.*') ? 'current-menu' : '' }}">
                                    <a href="{{ route('tours.index') }}" class="nav-link">
                                        Tours <span class="icon icon-CaretDown"></span>
                                    </a>
                                    <ul class="sub-menu shadow-dropdown">
                                        <li class="sub-item">
                                            <a href="{{ route('tours.index') }}">All tours</a>
                                        </li>
                                        @foreach ($navCategories as $category)
                                            <li class="sub-item">
                                                <a href="{{ route('tours.index', ['category' => $category->slug]) }}">
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>

                                <li class="menu-item flex-center gap-1 h-full {{ request()->routeIs('destinations.*') ? 'current-menu' : '' }}">
                                    <a href="{{ route('destinations.index') }}" class="nav-link">
                                        Destinations <span class="icon icon-CaretDown"></span>
                                    </a>
                                    <ul class="sub-menu shadow-dropdown">
                                        <li class="sub-item">
                                            <a href="{{ route('destinations.index') }}">All destinations</a>
                                        </li>
                                        @foreach ($navDestinations as $destination)
                                            <li class="sub-item">
                                                <a href="{{ route('destinations.show', $destination) }}">
                                                    {{ $destination->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>

                                <li class="menu-item flex-center gap-1 h-full {{ request()->routeIs('about') ? 'current-menu' : '' }}">
                                    <a href="{{ route('about') }}" class="nav-link">About</a>
                                </li>

                                <li class="menu-item flex-center gap-1 h-full {{ request()->routeIs('contact') ? 'current-menu' : '' }}">
                                    <a href="{{ route('contact') }}" class="nav-link">Contact</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="header-ct-right flex-end gap-4 z-5">
                {{-- color-ic paints the icons white, which is only readable
                     over the transparent hero header. --}}
                <ul class="wrap-login-menu {{ $overlay ? 'color-ic' : '' }}">
                    <li class="search">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalSearch" aria-label="Search tours">
                            <span class="icon icon-search"></span>
                        </a>
                    </li>
                    <li class="heart">
                        <a href="{{ route('wishlist') }}" aria-label="Wishlist" class="gt-wishlist-link">
                            <span class="icon icon-heart"></span>
                            <span class="gt-wishlist-count" data-wishlist-count hidden>0</span>
                        </a>
                    </li>
                    @auth
                        <li class="notif">
                            <x-notification-bell />
                        </li>
                    @endauth
                    <li class="login">
                        @auth
                            <span class="icon icon-user"></span>
                            <a href="{{ auth()->user()->isStaff() ? route('admin.dashboard') : route('account.bookings') }}">
                                {{ str(auth()->user()->name)->before(' ') }}
                            </a>
                            /
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('gt-logout').submit();">Log out</a>
                            <form id="gt-logout" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        @else
                            <span class="icon icon-user"></span>
                            <a href="{{ route('login') }}">Login</a>
                            /
                            <a href="{{ route('register') }}">Register</a>
                        @endauth
                    </li>
                </ul>
                <div class="toggle-mobile">
                    <span class="icon icon-list {{ $overlay ? 'text-white' : '' }}"></span>
                </div>
            </div>
        </div>

        <div class="overlay"></div>
        <div class="close-btn flex-center"><span class="icon-X"></span></div>

        <div class="mobile-menu">
            <div class="menu-box">
                <div class="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/logo/logo.svg') }}" alt="{{ config('app.name') }}">
                    </a>
                </div>
                <div class="inner-menu">
                    <div class="nav-item">
                        <a href="{{ route('home') }}" class="mobile-dropdown">
                            <span class="nav-text">Home</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="#" class="mobile-dropdown">
                            <span class="nav-text">Tours</span>
                            <span class="mb-icon icon-CaretDown"></span>
                        </a>
                        <ul class="mb-sub-menu">
                            <li><a href="{{ route('tours.index') }}" class="sub-link">All tours</a></li>
                            @foreach ($navCategories as $category)
                                <li>
                                    <a href="{{ route('tours.index', ['category' => $category->slug]) }}" class="sub-link">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="nav-item">
                        <a href="#" class="mobile-dropdown">
                            <span class="nav-text">Destinations</span>
                            <span class="mb-icon icon-CaretDown"></span>
                        </a>
                        <ul class="mb-sub-menu">
                            <li><a href="{{ route('destinations.index') }}" class="sub-link">All destinations</a></li>
                            @foreach ($navDestinations as $destination)
                                <li>
                                    <a href="{{ route('destinations.show', $destination) }}" class="sub-link">
                                        {{ $destination->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('about') }}" class="mobile-dropdown"><span class="nav-text">About</span></a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('wishlist') }}" class="mobile-dropdown"><span class="nav-text">Wishlist</span></a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('contact') }}" class="mobile-dropdown"><span class="nav-text">Contact</span></a>
                    </div>
                </div>

                <div class="inner-info">
                    <h4 class="title-info">Need help?</h4>
                    <div class="list-info d-flex flex-col">
                        <div class="phone d-flex">
                            <div class="icon flex-center">
                                <img src="{{ asset('assets/images/icons/phone-call.svg') }}" alt="Phone">
                            </div>
                            <div class="content">
                                <p class="label">Toll-free call</p>
                                <a href="tel:+12295550109">(229) 555-0109</a>
                            </div>
                        </div>
                        <div class="email d-flex">
                            <div class="icon flex-center">
                                <img src="{{ asset('assets/images/icons/mail.svg') }}" alt="Email">
                            </div>
                            <div class="content">
                                <p class="label">Email</p>
                                <a href="mailto:hello@globetrek.travel">hello@globetrek.travel</a>
                            </div>
                        </div>
                        <div class="address d-flex">
                            <div class="icon flex-center">
                                <img src="{{ asset('assets/images/icons/place.svg') }}" alt="Address">
                            </div>
                            <div class="content">
                                <p class="label">Office</p>
                                <a href="#">32 Rivington Street, London EC2A 3LX</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
