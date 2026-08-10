@php $current = $pages[$page]; @endphp

<x-layouts.admin :title="'Pages · '.$current['label']">

    <x-admin.page-header title="Pages"
        subtitle="The editable pages of the site. Change the words and photos, or hide a section entirely.">
        <a href="{{ route($current['route']) }}" class="adm-btn adm-btn--ghost" target="_blank" rel="noopener">
            View {{ Str::lower($current['label']) }}
        </a>
    </x-admin.page-header>

    <div class="adm-tabs" role="tablist">
        @foreach ($pages as $key => $definition)
            <a href="{{ route('admin.pages.index', $key) }}" role="tab"
                @class(['adm-tabs__tab', 'is-active' => $key === $page])
                @if ($key === $page) aria-selected="true" @endif>
                {{ $definition['label'] }}
            </a>
        @endforeach
    </div>

    <p class="adm-hint">{{ $current['description'] }}</p>

    <div class="adm-sections">
        @foreach ($definitions as $key => $definition)
            @php $section = $sections[$key]; @endphp

            <div class="adm-section-row {{ $section->is_visible ? '' : 'is-hidden' }}">
                <span class="adm-section-row__index">{{ $loop->iteration }}</span>

                <div class="adm-section-row__main">
                    <div class="adm-section-row__title">{{ $definition['label'] }}</div>
                    <p class="adm-section-row__sub">
                        {{ $section->heading ? '“'.$section->heading.'” — ' : '' }}{{ $definition['description'] }}
                    </p>
                </div>

                <div class="adm-section-row__actions">
                    <x-ui.badge :tone="$section->is_visible ? 'success' : 'muted'">
                        {{ $section->is_visible ? 'Visible' : 'Hidden' }}
                    </x-ui.badge>

                    <form action="{{ route('admin.pages.toggle', [$page, $key]) }}" method="POST" class="adm-inline-form">
                        @csrf @method('PATCH')
                        <button type="submit" class="adm-btn adm-btn--ghost adm-btn--sm">
                            {{ $section->is_visible ? 'Hide' : 'Show' }}
                        </button>
                    </form>

                    <a href="{{ route('admin.pages.edit', [$page, $key]) }}" class="adm-btn adm-btn--sm">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
