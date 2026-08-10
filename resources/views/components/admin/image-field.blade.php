@props([
    'name',
    'label' => null,
    'value' => null,
    'hint' => null,
    'required' => false,
    'span' => 6,
    'ratio' => '3 / 2',
])

@php
    $id = 'f-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $current = old(str_replace(['[', ']'], ['.', ''], $name), $value);
    $label ??= Str::of($name)->replace(['_', '[]'], [' ', ''])->title()->value();
    $invalid = $errors->has(str_replace(['[', ']'], ['.', ''], $name));
@endphp

{{-- The value is a public path string; the picker writes it into the hidden
     input and repaints the preview. Nothing here needs the admin to know that. --}}
<div class="adm-field adm-col-{{ $span }} {{ $invalid ? 'is-invalid' : '' }}">
    <label for="{{ $id }}">
        {{ $label }} @if ($required) <span class="adm-req">*</span> @endif
    </label>

    <div class="adm-image-field {{ $current ? '' : 'is-empty' }}" data-image-field style="--adm-image-ratio: {{ $ratio }}">
        <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $current }}" data-image-value
            @required($required)>

        <button type="button" class="adm-image-field__preview" data-image-open
            aria-label="{{ $current ? 'Change '.Str::lower($label) : 'Choose '.Str::lower($label) }}">
            <img src="{{ $current ? asset($current) : '' }}" alt="" data-image-preview
                {{ $current ? '' : 'hidden' }}>
            <span class="adm-image-field__placeholder" data-image-placeholder {{ $current ? 'hidden' : '' }}>
                <i class="icon icon-Category"></i>
                <span>Choose an image</span>
            </span>
        </button>

        <div class="adm-image-field__actions">
            <button type="button" class="adm-btn adm-btn--ghost adm-btn--sm" data-image-open>
                <span data-image-action-label>{{ $current ? 'Change' : 'Choose image' }}</span>
            </button>
            <button type="button" class="adm-btn adm-btn--ghost adm-btn--sm adm-image-field__clear"
                data-image-clear {{ $current ? '' : 'hidden' }}>Remove</button>
        </div>
    </div>

    @if ($hint)
        <p class="adm-hint">{{ $hint }}</p>
    @endif

    @error(str_replace(['[', ']'], ['.', ''], $name))
        <p class="adm-error">{{ $message }}</p>
    @enderror
</div>
