@props([
    'title' => null,
    'description' => 'GlobeTrek curates small-group tours and tailor-made travel across 18 destinations worldwide.',
    'header' => 'solid',
])

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name').' — Small-group tours worth taking' }}</title>
    <meta name="description" content="{{ $description }}">

    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/font-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/boostrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/nouislider.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/globetrek.css') }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/logo/favicon.png') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('assets/images/logo/favicon.png') }}">
</head>

<body>
    <div class="preload preload-container">
        <div class="preload-logo">
            <div class="spinner"></div>
            <img src="{{ asset('assets/images/logo/destination.png') }}" alt="Loading GlobeTrek">
        </div>
    </div>

    <x-layout.header :variant="$header" />

    {{ $hero ?? '' }}

    <main id="content">
        {{ $slot }}
    </main>

    <x-layout.footer />

    <x-layout.search-modal />

    <x-ui.toast />

    <button class="backtotop" id="backtotop" aria-label="Back to top">
        <span class="border-progress"></span>
        <span class="icon icon-arrow-up"></span>
    </button>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/nouislider.min.js') }}"></script>
    <script src="{{ asset('assets/js/fancybox.umd.js') }}"></script>
    <script src="{{ asset('assets/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets/js/controls.js') }}" defer></script>
    <script src="{{ asset('assets/js/globetrek.js') }}" defer></script>
    @stack('scripts')
</body>

</html>
