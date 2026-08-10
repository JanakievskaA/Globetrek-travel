@props([
    'title',
    'subtitle' => null,
    'align' => 'center',
    'tag' => 'h2',
])

<div {{ $attributes->merge([
    'class' => 'box-title wow animate__animated animate__fadeInUp '.($align === 'center' ? 'text-center' : ''),
]) }}>
    <{{ $tag }} class="title h1">{{ $title }}</{{ $tag }}>
    @if ($subtitle)
        <div class="subtitle text-color desc">{{ $subtitle }}</div>
    @endif
    {{ $slot }}
</div>
