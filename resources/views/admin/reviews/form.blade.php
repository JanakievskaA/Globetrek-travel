<x-layouts.admin title="Edit review">

    <x-admin.page-header :title="'Edit review by '.$review->author_name" :subtitle="$review->tour->title">
        <a href="{{ route('admin.reviews.index') }}" class="adm-btn adm-btn--ghost">Cancel</a>
    </x-admin.page-header>

    <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
        @csrf @method('PUT')

        <div class="adm-panel">
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="author_name" label="Author name" :value="$review->author_name" span="6" required />
                    <x-admin.field name="author_email" label="Author email" type="email"
                        :value="$review->author_email" span="6" />

                    <x-admin.field name="rating" type="select" :value="$review->rating"
                        :options="[5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']"
                        span="4" required />
                    <x-admin.field name="status" type="select" :value="$review->status->value"
                        :options="$statuses" span="4" required />
                    <x-admin.field name="helpful_count" label="Helpful votes" type="number" min="0"
                        :value="$review->helpful_count" span="4" required />

                    <x-admin.field name="title" label="Headline" :value="$review->title" span="12" />
                    <x-admin.field name="body" label="Review text" type="textarea" rows="7"
                        :value="$review->body" span="12" required />

                    <x-admin.field name="is_featured" label="Use as a homepage testimonial" type="checkbox"
                        :value="$review->is_featured" span="12" />
                </div>
            </div>
            <div class="adm-form-actions">
                <a href="{{ route('admin.reviews.index') }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">Save changes</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
