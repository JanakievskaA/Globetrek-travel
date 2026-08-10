<x-layouts.admin title="Tours">

    <x-admin.page-header title="Tours" :subtitle="number_format($tours->total()).' tours in the catalogue'">
        <a href="{{ route('admin.tours.create') }}" class="adm-btn">+ New tour</a>
    </x-admin.page-header>

    <x-admin.data-table :paginator="$tours" empty="No tours match these filters."
        :headers="['Tour', 'Destination', 'Category', 'Price', 'Duration', 'Rating', 'Status', ['label' => 'Actions', 'align' => 'right']]">

        <x-slot:filters>
            <form method="GET" action="{{ route('admin.tours.index') }}" data-auto-filter class="adm-filters"
                style="border:0;padding:0;background:none;width:100%">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search tours…" class="adm-grow">

                <select name="destination">
                    <option value="">All destinations</option>
                    @foreach ($destinations as $id => $name)
                        <option value="{{ $id }}" @selected(request('destination') == $id)>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="category">
                    <option value="">All categories</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="status">
                    <option value="">Any status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="featured">
                    <option value="">Featured?</option>
                    <option value="1" @selected(request('featured') === '1')>Featured only</option>
                    <option value="0" @selected(request('featured') === '0')>Not featured</option>
                </select>

                <select name="sort">
                    <option value="created_at" @selected(request('sort', 'created_at') === 'created_at')>Newest</option>
                    <option value="title" @selected(request('sort') === 'title')>Title</option>
                    <option value="price" @selected(request('sort') === 'price')>Price</option>
                    <option value="rating_avg" @selected(request('sort') === 'rating_avg')>Rating</option>
                </select>

                @if (request()->hasAny(['q', 'destination', 'category', 'status', 'featured', 'sort']))
                    <a href="{{ route('admin.tours.index') }}" class="adm-btn adm-btn--ghost">Reset</a>
                @endif
            </form>
        </x-slot:filters>

        @foreach ($tours as $tour)
            <tr>
                <td>
                    <div class="adm-table__media">
                        <img src="{{ asset($tour->image) }}" alt="" class="adm-table__thumb">
                        <div>
                            <a href="{{ route('admin.tours.edit', $tour) }}" class="adm-table__title">
                                {{ Str::limit($tour->title, 46) }}
                            </a>
                            <div class="adm-table__sub">
                                {{ $tour->is_featured ? 'Featured · ' : '' }}{{ number_format($tour->bookings_count) }} bookings
                            </div>
                        </div>
                    </div>
                </td>
                <td>{{ $tour->destination->name }}<div class="adm-table__sub">{{ $tour->destination->country }}</div></td>
                <td>{{ $tour->category->name }}</td>
                <td>
                    @if ($tour->is_on_sale)
                        <strong>${{ number_format($tour->sale_price, 0) }}</strong>
                        <div class="adm-table__sub"><s>${{ number_format($tour->price, 0) }}</s></div>
                    @else
                        <strong>${{ number_format($tour->price, 0) }}</strong>
                    @endif
                </td>
                <td>{{ $tour->short_duration }}</td>
                <td>
                    <span class="adm-stars">
                        @for ($i = 1; $i <= 5; $i++)<span class="{{ $i <= round($tour->rating_avg) ? '' : 'is-off' }}">★</span>@endfor
                    </span>
                    <div class="adm-table__sub">{{ number_format($tour->rating_avg, 1) }} ({{ $tour->reviews_count }})</div>
                </td>
                <td>
                    <x-ui.badge :tone="$tour->status->badge()">{{ $tour->status->label() }}</x-ui.badge>
                </td>
                <td>
                    <x-admin.row-actions :edit="route('admin.tours.edit', $tour)"
                        :destroy="route('admin.tours.destroy', $tour)"
                        :view="route('tours.show', $tour)"
                        :confirm="'Delete “'.$tour->title.'”? This also removes its gallery and itinerary.'" />
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
