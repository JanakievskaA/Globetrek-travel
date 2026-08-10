<x-layouts.app title="Wishlist">

    <x-ui.page-title title="Your wishlist" :breadcrumbs="['Wishlist' => null]" />

    <div class="flat-section flat-recommended">
        <div class="container">
            @if ($tours->isEmpty())
                <x-ui.empty-state title="Nothing saved yet"
                    message="Tap the heart on any tour to keep it here. Your list is stored in this browser.">
                    <a href="{{ route('tours.index') }}" class="tf-btn primary hover-1"><span>Browse tours</span></a>
                </x-ui.empty-state>
            @else
                <p class="subtitle text-color mb-6">
                    {{ $tours->count() }} saved {{ Str::plural('tour', $tours->count()) }}.
                </p>
                <div class="tf-grid-layout tf-col-3 lg-col-2 sm-col-1">
                    @foreach ($tours as $tour)
                        <x-tour.card :tour="$tour" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
