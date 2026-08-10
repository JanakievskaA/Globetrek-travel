<x-layouts.admin title="Destinations">

    <x-admin.page-header title="Destinations"
        :subtitle="number_format($destinations->total()).' destinations across the catalogue'">
        <a href="{{ route('admin.destinations.create') }}" class="adm-btn">+ New destination</a>
    </x-admin.page-header>

    <x-admin.data-table :paginator="$destinations" empty="No destinations match these filters."
        :headers="['Destination', 'Continent', 'Tours', 'Best season', 'Featured', 'Status', ['label' => 'Actions', 'align' => 'right']]">

        <x-slot:filters>
            <form method="GET" action="{{ route('admin.destinations.index') }}" data-auto-filter class="adm-filters"
                style="border:0;padding:0;background:none;width:100%">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search destinations…" class="adm-grow">
                <select name="continent">
                    <option value="">All continents</option>
                    @foreach ($continents as $item)
                        <option value="{{ $item }}" @selected(request('continent') === $item)>{{ $item }}</option>
                    @endforeach
                </select>
                <select name="active">
                    <option value="">Any status</option>
                    <option value="1" @selected(request('active') === '1')>Active</option>
                    <option value="0" @selected(request('active') === '0')>Hidden</option>
                </select>
                @if (request()->hasAny(['q', 'continent', 'active']))
                    <a href="{{ route('admin.destinations.index') }}" class="adm-btn adm-btn--ghost">Reset</a>
                @endif
            </form>
        </x-slot:filters>

        @foreach ($destinations as $destination)
            <tr>
                <td>
                    <div class="adm-table__media">
                        <img src="{{ asset($destination->image) }}" alt="" class="adm-table__thumb">
                        <div>
                            <a href="{{ route('admin.destinations.edit', $destination) }}" class="adm-table__title">
                                {{ $destination->name }}
                            </a>
                            <div class="adm-table__sub">{{ $destination->country }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $destination->continent ?? '—' }}</td>
                <td><strong>{{ $destination->tours_count }}</strong></td>
                <td>{{ $destination->best_season ?? '—' }}</td>
                <td>
                    @if ($destination->is_featured)
                        <x-ui.badge tone="warning">Featured</x-ui.badge>
                    @else
                        <span class="adm-hint">—</span>
                    @endif
                </td>
                <td>
                    <x-ui.badge :tone="$destination->is_active ? 'success' : 'muted'">
                        {{ $destination->is_active ? 'Active' : 'Hidden' }}
                    </x-ui.badge>
                </td>
                <td>
                    <x-admin.row-actions :edit="route('admin.destinations.edit', $destination)"
                        :destroy="route('admin.destinations.destroy', $destination)"
                        :view="route('destinations.show', $destination)"
                        :confirm="'Delete “'.$destination->name.'”?'" />
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
