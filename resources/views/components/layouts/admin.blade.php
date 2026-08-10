@props(['title' => 'Dashboard'])

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name') }} Admin</title>

    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/font-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/globetrek.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/favicon.png') }}">
</head>

<body class="adm">
    <div class="adm-shell">
        <x-admin.sidebar />

        <div class="adm-main">
            <x-admin.topbar :title="$title" />

            <div class="adm-content">
                {{ $slot }}
            </div>
        </div>
    </div>

    <div class="adm-scrim" data-sidebar-close></div>
    <x-ui.toast />
    <x-admin.media-modal />

    <script src="{{ asset('assets/js/controls.js') }}" defer></script>
    <script src="{{ asset('assets/js/admin.js') }}" defer></script>
</body>

</html>
