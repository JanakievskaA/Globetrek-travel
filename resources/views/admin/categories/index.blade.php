<x-layouts.admin title="Categories">

    <x-admin.page-header title="Categories" :subtitle="number_format($categories->total()).' tour types'">
        <a href="{{ route('admin.categories.create') }}" class="adm-btn">+ New category</a>
    </x-admin.page-header>

    <x-admin.data-table :paginator="$categories" empty="No categories match these filters."
        :headers="['Category', 'Description', 'Tours', 'Order', 'Featured', 'Status', ['label' => 'Actions', 'align' => 'right']]">

        <x-slot:filters>
            <form method="GET" action="{{ route('admin.categories.index') }}" data-auto-filter class="adm-filters"
                style="border:0;padding:0;background:none;width:100%">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search categories…" class="adm-grow">
                <select name="active">
                    <option value="">Any status</option>
                    <option value="1" @selected(request('active') === '1')>Active</option>
                    <option value="0" @selected(request('active') === '0')>Hidden</option>
                </select>
                @if (request()->hasAny(['q', 'active']))
                    <a href="{{ route('admin.categories.index') }}" class="adm-btn adm-btn--ghost">Reset</a>
                @endif
            </form>
        </x-slot:filters>

        @foreach ($categories as $category)
            <tr>
                <td>
                    <div class="adm-table__media">
                        @if ($category->image)
                            <img src="{{ asset($category->image) }}" alt="" class="adm-table__thumb">
                        @endif
                        <div>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="adm-table__title">
                                {{ $category->name }}
                            </a>
                            <div class="adm-table__sub">{{ $category->slug }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="adm-clamp-2 adm-hint">{{ $category->description }}</span></td>
                <td><strong>{{ $category->tours_count }}</strong></td>
                <td>{{ $category->sort_order }}</td>
                <td>
                    @if ($category->is_featured)
                        <x-ui.badge tone="warning">Featured</x-ui.badge>
                    @else
                        <span class="adm-hint">—</span>
                    @endif
                </td>
                <td>
                    <x-ui.badge :tone="$category->is_active ? 'success' : 'muted'">
                        {{ $category->is_active ? 'Active' : 'Hidden' }}
                    </x-ui.badge>
                </td>
                <td>
                    <x-admin.row-actions :edit="route('admin.categories.edit', $category)"
                        :destroy="route('admin.categories.destroy', $category)"
                        :view="route('tours.index', ['category' => $category->slug])"
                        :confirm="'Delete “'.$category->name.'”?'" />
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
