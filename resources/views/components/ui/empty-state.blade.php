@props([
    'title' => 'Nothing here yet',
    'message' => null,
    'icon' => 'assets/images/logo/nofind.png',
])

<div {{ $attributes->merge(['class' => 'gt-empty-state text-center']) }}>
    <img src="{{ asset($icon) }}" alt="" class="gt-empty-state__icon">
    <div class="h4 mt-4">{{ $title }}</div>
    @if ($message)
        <p class="subtitle text-color mt-2">{{ $message }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
