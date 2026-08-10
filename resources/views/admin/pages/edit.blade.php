@php
    use App\Support\PageSections;

    $fields = collect($definition['fields'] ?? []);
    $simple = $fields->reject(fn ($spec) => ($spec['type'] ?? 'text') === 'repeater');
    $repeaters = $fields->filter(fn ($spec) => ($spec['type'] ?? 'text') === 'repeater');
@endphp

<x-layouts.admin :title="$pageLabel.' · '.$definition['label']">

    <x-admin.page-header :title="$definition['label']" :subtitle="$definition['description']">
        <a href="{{ route('admin.pages.index', $page) }}" class="adm-btn adm-btn--ghost">Back to {{ Str::lower($pageLabel) }}</a>
        <a href="{{ route($pages[$page]['route']) }}" class="adm-btn adm-btn--ghost" target="_blank" rel="noopener">View page</a>
    </x-admin.page-header>

    <form action="{{ route('admin.pages.update', [$page, $key]) }}" method="POST">
        @csrf @method('PUT')

        <div class="adm-panel">
                <div class="adm-panel__head"><div class="adm-panel__title">Section</div></div>
                <div class="adm-panel__body">
                    <div class="adm-form-grid">
                        @if (PageSections::hasHeading($key))
                            <x-admin.field name="heading" label="Heading" :value="$section->heading"
                                :span="PageSections::hasSubtitle($key) ? 6 : 12" />
                        @endif

                        @if (PageSections::hasSubtitle($key))
                            <x-admin.field name="subtitle" label="Sentence under the heading" type="textarea"
                                rows="2" :value="$section->subtitle" span="6" />
                        @endif

                        <x-admin.field name="is_visible" label="Show this section on the page" type="checkbox"
                            :value="$section->is_visible" span="12" />
                    </div>
                </div>
            </div>

        @if ($simple->isNotEmpty())
            <div class="adm-panel">
                <div class="adm-panel__head"><div class="adm-panel__title">Content</div></div>
                <div class="adm-panel__body">
                    <div class="adm-form-grid">
                        @foreach ($simple as $field => $spec)
                            <x-admin.dynamic-field :name="'data['.$field.']'" :spec="$spec"
                                :value="$section->value($field)" :destinations="$destinations" />
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @foreach ($repeaters as $field => $spec)
            @php
                $rows = $section->rows($field);
                $rowLabel = $spec['row_label'] ?? 'Item';
                $prefix = 'data['.$field.']';
            @endphp

            <div class="adm-panel" data-repeater data-repeater-max="{{ $spec['max'] ?? 12 }}">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">{{ $spec['label'] }}</div>
                    <button type="button" class="adm-btn adm-btn--ghost adm-btn--sm" data-repeater-add>
                        + Add {{ Str::lower($rowLabel) }}
                    </button>
                </div>

                <div class="adm-panel__body">
                    @if (! empty($spec['hint']))
                        <p class="adm-hint" style="margin-top:0">{{ $spec['hint'] }}</p>
                    @endif

                    <div class="adm-repeater" data-repeater-list>
                        @foreach ($rows as $index => $row)
                            @include('admin.partials.repeater-row', [
                                'index' => $index,
                                'row' => $row,
                                'fields' => $spec['fields'],
                                'prefix' => $prefix,
                                'rowLabel' => $rowLabel,
                                'destinations' => $destinations,
                            ])
                        @endforeach
                    </div>

                    <p class="adm-repeater__empty" data-repeater-empty @if (count($rows)) hidden @endif>
                        Nothing here yet — use “Add {{ Str::lower($rowLabel) }}”.
                    </p>
                </div>

                <template data-repeater-template>
                    @include('admin.partials.repeater-row', [
                        'index' => '__index__',
                        'row' => [],
                        'fields' => $spec['fields'],
                        'prefix' => $prefix,
                        'rowLabel' => $rowLabel,
                        'destinations' => $destinations,
                    ])
                </template>
            </div>
        @endforeach

        <div class="adm-panel">
            <div class="adm-form-actions">
                <a href="{{ route('admin.pages.index', $page) }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">Save changes</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
