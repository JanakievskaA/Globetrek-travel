<x-layouts.admin title="Review">

    <x-admin.page-header :title="'Review by '.$review->author_name" :subtitle="$review->tour->title">
        <a href="{{ route('admin.reviews.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
        <a href="{{ route('admin.reviews.edit', $review) }}" class="adm-btn">Edit</a>
    </x-admin.page-header>

    <div class="adm-panel" style="max-width:820px">
        <div class="adm-panel__head">
            <div class="adm-panel__title">
                <span class="adm-stars">
                    @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? '' : 'is-off' }}">★</span>@endfor
                </span>
                {{ $review->title }}
            </div>
            <x-ui.badge :tone="$review->status->badge()">{{ $review->status->label() }}</x-ui.badge>
        </div>
        <div class="adm-panel__body">
            <p style="line-height:1.75">{{ $review->body }}</p>

            <div class="adm-stack" style="margin-top:22px">
                <div class="adm-kv">
                    <span class="adm-kv__key">Author</span>
                    <span class="adm-kv__val">{{ $review->author_name }} ({{ $review->author_email }})</span>
                </div>
                <div class="adm-kv">
                    <span class="adm-kv__key">Submitted</span>
                    <span class="adm-kv__val">{{ $review->created_at->format('j F Y, H:i') }}</span>
                </div>
                <div class="adm-kv">
                    <span class="adm-kv__key">Marked helpful</span>
                    <span class="adm-kv__val">{{ $review->helpful_count }} times</span>
                </div>
                <div class="adm-kv">
                    <span class="adm-kv__key">Verified account</span>
                    <span class="adm-kv__val">{{ $review->user_id ? 'Yes' : 'No' }}</span>
                </div>
            </div>

            <div style="display:flex;gap:8px;margin-top:22px">
                @foreach (['approved' => 'Approve', 'rejected' => 'Reject', 'pending' => 'Send back to queue'] as $value => $label)
                    @continue($review->status->value === $value)
                    <form action="{{ route('admin.reviews.status', $review) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $value }}">
                        <button type="submit" class="adm-btn {{ $value === 'approved' ? '' : 'adm-btn--ghost' }}">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.admin>
