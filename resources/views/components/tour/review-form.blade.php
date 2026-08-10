@props(['tour'])

<div class="gt-card mt-10">
    <div class="property-topic h3">Write a review</div>
    <p class="text-color mt-2">
        Your email is never published. Reviews appear once a moderator has checked them.
    </p>

    <form action="{{ route('reviews.store', $tour) }}" method="POST" class="gt-stack mt-6">
        @csrf

        <div class="gt-field">
            <label>Your rating</label>
            <div class="gt-rating-picker" data-rating-picker>
                <input type="hidden" name="rating" value="{{ old('rating', 5) }}">
                @for ($i = 1; $i <= 5; $i++)
                    <span class="icon icon-star" data-star="{{ $i }}" role="button"
                        aria-label="{{ $i }} {{ Str::plural('star', $i) }}"></span>
                @endfor
            </div>
            @error('rating') <p class="gt-form-error">{{ $message }}</p> @enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="gt-field">
                    <label for="rv-name">Name *</label>
                    <input type="text" id="rv-name" name="author_name" required
                        value="{{ old('author_name', auth()->user()?->name) }}">
                    @error('author_name') <p class="gt-form-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="gt-field">
                    <label for="rv-email">Email *</label>
                    <input type="email" id="rv-email" name="author_email" required
                        value="{{ old('author_email', auth()->user()?->email) }}">
                    @error('author_email') <p class="gt-form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="gt-field">
            <label for="rv-title">Headline</label>
            <input type="text" id="rv-title" name="title" value="{{ old('title') }}"
                placeholder="Sum it up in a few words">
        </div>

        <div class="gt-field">
            <label for="rv-body">Review *</label>
            <textarea id="rv-body" name="body" rows="5" required
                placeholder="What worked, what didn't, and what you'd tell a friend.">{{ old('body') }}</textarea>
            @error('body') <p class="gt-form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <button type="submit" class="tf-btn primary hover-1"><span>Post review</span></button>
        </div>
    </form>
</div>
