<x-layouts.admin title="Reviews">

    <x-admin.page-header title="Reviews" :subtitle="number_format($reviews->total()).' matching reviews'" />

    <div class="adm-stats">
        <x-admin.stat-card label="All reviews" value="{{ number_format($totals['all']) }}" icon="icon-star" tone="info" />
        <x-admin.stat-card label="Awaiting moderation" value="{{ $totals['pending'] }}" icon="icon-clock"
            :tone="$totals['pending'] > 0 ? 'warn' : 'ok'" />
        <x-admin.stat-card label="Published" value="{{ number_format($totals['approved']) }}" icon="icon-check" tone="ok" />
        <x-admin.stat-card label="Average score" value="{{ $totals['average'] }} / 5" icon="icon-range" tone="brand" />
    </div>

    <x-admin.data-table :paginator="$reviews" empty="No reviews match these filters."
        :headers="['Author', 'Tour', 'Rating', 'Review', 'Date', 'Status', ['label' => 'Actions', 'align' => 'right']]">

        <x-slot:filters>
            <form method="GET" action="{{ route('admin.reviews.index') }}" data-auto-filter class="adm-filters"
                style="border:0;padding:0;background:none;width:100%">
                <input type="search" name="q" value="{{ request('q') }}"
                    placeholder="Author, text or tour…" class="adm-grow">
                <select name="status">
                    <option value="">Any status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="rating">
                    <option value="">Any rating</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(request('rating') == $i)>{{ $i }} stars</option>
                    @endfor
                </select>
                @if (request()->hasAny(['q', 'status', 'rating']))
                    <a href="{{ route('admin.reviews.index') }}" class="adm-btn adm-btn--ghost">Reset</a>
                @endif
            </form>
        </x-slot:filters>

        @foreach ($reviews as $review)
            <tr>
                <td>
                    <div class="adm-table__media">
                        <img src="{{ $review->avatar_url }}" alt="" class="adm-table__thumb adm-table__thumb--round">
                        <div>
                            <span class="adm-table__title">{{ $review->author_name }}</span>
                            <div class="adm-table__sub">{{ $review->author_email }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="adm-clamp-2">{{ $review->tour->title }}</span></td>
                <td>
                    <span class="adm-stars">
                        @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? '' : 'is-off' }}">★</span>@endfor
                    </span>
                </td>
                <td>
                    @if ($review->title)
                        <span class="adm-table__title">{{ $review->title }}</span>
                    @endif
                    <div class="adm-clamp-2 adm-hint">{{ $review->body }}</div>
                </td>
                <td>{{ $review->created_at->format('j M Y') }}</td>
                <td>
                    <form action="{{ route('admin.reviews.status', $review) }}" method="POST" class="adm-inline-form">
                        @csrf @method('PATCH')
                        <select name="status" aria-label="Change status">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($review->status->value === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </td>
                <td>
                    <x-admin.row-actions :edit="route('admin.reviews.edit', $review)"
                        :destroy="route('admin.reviews.destroy', $review)"
                        :confirm="'Delete the review by '.$review->author_name.'?'">
                        <a href="{{ route('admin.reviews.show', $review) }}" class="adm-icon-btn" title="Read">
                            <i class="icon icon-Search"></i>
                        </a>
                    </x-admin.row-actions>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
