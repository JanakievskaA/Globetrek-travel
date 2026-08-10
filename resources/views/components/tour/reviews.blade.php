@props(['tour', 'reviews', 'breakdown' => []])

<div id="reviews">
    <div class="property-topic mb-6 h3">
        Traveller reviews
        <span class="subtitle text-color">({{ number_format($tour->reviews_count) }})</span>
    </div>

    @if ($tour->reviews_count === 0)
        <x-ui.empty-state title="No reviews yet"
            message="Be the first to write about this tour once you have travelled." />
    @else
        <div class="gt-review-summary">
            <div class="gt-review-score">
                <div class="gt-review-score__value">{{ number_format($tour->rating_avg, 1) }}</div>
                <x-ui.rating :value="$tour->rating_avg" :show-score="false" class="rating justify-content-center mt-2" />
                <p class="subtitle text-color mt-2">{{ number_format($tour->reviews_count) }} reviews</p>
            </div>

            <div class="gt-review-bars">
                @foreach ($breakdown as $star => $row)
                    <div class="gt-review-bar">
                        <span>{{ $star }} {{ Str::plural('star', $star) }}</span>
                        <span class="gt-review-bar__track">
                            <span class="gt-review-bar__fill" style="width: {{ $row['percent'] }}%"></span>
                        </span>
                        <span>{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="wrap-review mt-8">
            @foreach ($reviews as $review)
                <div class="gt-review">
                    <img src="{{ $review->avatar_url }}" alt="{{ $review->author_name }}" class="gt-review__avatar"
                        loading="lazy">
                    <div class="flex-1">
                        <div class="gt-review__head">
                            <div>
                                <div class="gt-review__name">{{ $review->author_name }}</div>
                                <div class="gt-review__date">
                                    {{ $review->created_at->format('j F Y') }}
                                    @if ($review->user_id)
                                        · <span class="text_primary">Verified traveller</span>
                                    @endif
                                </div>
                            </div>
                            <x-ui.rating :value="$review->rating" :show-score="false" />
                        </div>

                        @if ($review->title)
                            <div class="h5 mt-3">{{ $review->title }}</div>
                        @endif

                        <p class="gt-review__body">{{ $review->body }}</p>

                        <div class="d-flex gap-4 mt-3">
                            <span class="gt-hint">
                                <img src="{{ asset('assets/images/icons/like.svg') }}" alt="" width="16">
                                {{ $review->helpful_count }} found this helpful
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $reviews->links() }}
        </div>
    @endif
</div>
