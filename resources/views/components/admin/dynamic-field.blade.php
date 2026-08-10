@props([
    'name',
    'spec' => [],
    'value' => null,
    'destinations' => [],
])

{{-- Renders one field from the App\Support\PageSections registry. Keeping the
     mapping here means a new field type is a change in two files, not ten. --}}
@php $type = $spec['type'] ?? 'text'; @endphp

@switch($type)
    @case('image')
        <x-admin.image-field :name="$name" :label="$spec['label'] ?? null" :value="$value"
            :hint="$spec['hint'] ?? null" :span="$spec['span'] ?? 12" :ratio="$spec['ratio'] ?? '3 / 2'" />
        @break

    @case('textarea')
        <x-admin.field type="textarea" :rows="$spec['rows'] ?? 3"
            :name="$name" :label="$spec['label'] ?? null" :hint="$spec['hint'] ?? null"
            :span="$spec['span'] ?? 12" :value="$value" />
        @break

    @case('destination')
        <x-admin.field type="select" :options="$destinations" placeholder="— none —"
            :name="$name" :label="$spec['label'] ?? null" :hint="$spec['hint'] ?? null"
            :span="$spec['span'] ?? 12" :value="$value" />
        @break

    @case('number')
        <x-admin.field type="number" min="0"
            :name="$name" :label="$spec['label'] ?? null" :hint="$spec['hint'] ?? null"
            :span="$spec['span'] ?? 12" :value="$value" />
        @break

    @case('url')
        <x-admin.field type="url" placeholder="https://…"
            :name="$name" :label="$spec['label'] ?? null" :hint="$spec['hint'] ?? null"
            :span="$spec['span'] ?? 12" :value="$value" />
        @break

    @default
        <x-admin.field :name="$name" :label="$spec['label'] ?? null" :hint="$spec['hint'] ?? null"
            :span="$spec['span'] ?? 12" :value="$value" />
@endswitch
