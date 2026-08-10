@props([
    'title',
    'breadcrumbs' => [],
    'image' => null,
])

<div class="page-title {{ $image ? 'gt-page-title--image' : '' }}"
    @if ($image) style="background-image: linear-gradient(rgba(15,23,42,.55), rgba(15,23,42,.55)), url('{{ asset($image) }}')" @endif>
    <div class="container">
        <div class="text-center title wow animate__animated animate__fadeInUp h1">{{ $title }}</div>
        <div class="breadcrumb-content wow animate__animated animate__fadeInUp">
            <ul class="breadcrumb justify-content-center">
                <li><a href="{{ route('home') }}">Home</a></li>
                @foreach ($breadcrumbs as $label => $url)
                    <li class="gt-crumb-sep">/</li>
                    <li>
                        @if ($url)
                            <a href="{{ $url }}">{{ $label }}</a>
                        @else
                            <span class="current">{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
        {{ $slot }}
    </div>
</div>
