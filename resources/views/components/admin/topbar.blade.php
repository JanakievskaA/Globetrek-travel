@props(['title' => 'Dashboard'])

@php $user = auth()->user(); @endphp

<header class="adm-topbar">
    <button type="button" class="adm-burger" data-sidebar-open aria-label="Open menu">
        <i class="icon icon-list"></i>
    </button>

    <div class="adm-topbar__title">
        <h1>{{ $title }}</h1>
    </div>

    <div class="adm-topbar__actions">
        <a href="{{ route('home') }}" class="adm-btn adm-btn--ghost" target="_blank" rel="noopener">
            View site
        </a>

        @if ($user)
            <x-notification-bell />

            <div class="adm-user">
                <img src="{{ $user->avatar_url }}" alt="" class="adm-user__avatar">
                <div class="adm-user__meta">
                    <div class="adm-user__name">{{ $user->name }}</div>
                    <div class="adm-user__role">{{ $user->role->label() }}</div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="adm-btn adm-btn--ghost">Log out</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="adm-btn">Log in</a>
        @endif
    </div>
</header>
