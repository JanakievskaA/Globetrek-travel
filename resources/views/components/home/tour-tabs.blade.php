@props([
    'featuredTours' => collect(),
    'toursByCategory' => collect(),
    'tabCategories' => collect(),
    'section' => null,
])

<div class="flat-section flat-recommended">
    <div class="container">
        <x-ui.section-title :title="$section?->heading ?? 'Our best-selling tours'"
            :subtitle="$section?->subtitle ?? 'The departures travellers book most, rated by the people who actually went.'" />

        <div class="flat-tab-recommend">
            <div class="row">
                <ul class="nav-tab wow animate__animated animate__fade" role="tablist">
                    <li class="nav-tab-item" role="presentation">
                        <a href="#tab-all" class="nav-link-item active" data-bs-toggle="tab" role="tab"
                            aria-controls="tab-all" aria-selected="true">View all</a>
                        <div class="total-tour"><span>{{ $featuredTours->count() }} tours</span></div>
                    </li>
                    @foreach ($tabCategories as $category)
                        @continue(($toursByCategory[$category->slug] ?? collect())->isEmpty())
                        <li class="nav-tab-item" role="presentation">
                            <a href="#tab-{{ $category->slug }}" class="nav-link-item" data-bs-toggle="tab"
                                role="tab" aria-controls="tab-{{ $category->slug }}" aria-selected="false"
                                tabindex="-1">{{ $category->name }}</a>
                            <div class="total-tour"><span>{{ $category->tours_count }} tours</span></div>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                        <div class="tf-grid-layout tf-col-4 xl-col-3 lg-col-2 sm-col-1">
                            @foreach ($featuredTours as $tour)
                                <x-tour.card :tour="$tour" />
                            @endforeach
                        </div>
                    </div>

                    @foreach ($tabCategories as $category)
                        @php $items = ($toursByCategory[$category->slug] ?? collect())->take(8); @endphp
                        @continue($items->isEmpty())
                        <div class="tab-pane fade" id="tab-{{ $category->slug }}" role="tabpanel">
                            <div class="tf-grid-layout tf-col-4 xl-col-3 lg-col-2 sm-col-1">
                                @foreach ($items as $tour)
                                    <x-tour.card :tour="$tour" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('tours.index') }}" class="tf-btn primary hover-1">
                    <span>{{ $section?->value('button_label') ?? 'Browse all tours' }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
