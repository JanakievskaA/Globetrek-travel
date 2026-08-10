@props([
    'title',
    'subtitle' => null,
])

<div class="adm-page-head">
    <div>
        <h2 class="adm-page-head__title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="adm-page-head__sub">{{ $subtitle }}</p>
        @endif
    </div>
    @if (! $slot->isEmpty())
        <div class="adm-page-head__actions">{{ $slot }}</div>
    @endif
</div>
