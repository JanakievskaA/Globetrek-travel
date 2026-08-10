@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'rows' => 4,
    'span' => 6,
])

@php
    $id = 'f-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name);
    $current = old(str_replace(['[', ']'], ['.', ''], $name), $value);
    $label ??= Str::of($name)->replace(['_', '[]'], [' ', ''])->title()->value();
    $invalid = $errors->has(str_replace(['[', ']'], ['.', ''], $name));
@endphp

<div class="adm-field adm-col-{{ $span }} {{ $invalid ? 'is-invalid' : '' }}">
    @if ($type !== 'checkbox')
        <label for="{{ $id }}">
            {{ $label }} @if ($required) <span class="adm-req">*</span> @endif
        </label>
    @endif

    @switch($type)
        @case('textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
                placeholder="{{ $placeholder }}" @required($required)>{{ $current }}</textarea>
            @break

        @case('select')
            <select id="{{ $id }}" name="{{ $name }}" @required($required)>
                @if (! $required)
                    <option value="">{{ $placeholder ?? '— none —' }}</option>
                @endif
                @foreach ($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
            @break

        @case('checkbox')
            <label class="adm-switch" for="{{ $id }}">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($current)>
                <span class="adm-switch__track"><span class="adm-switch__thumb"></span></span>
                <span class="adm-switch__label">{{ $label }}</span>
            </label>
            @break

        @case('list')
            {{-- Newline-separated editor for the JSON list columns. --}}
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
                placeholder="{{ $placeholder }}">{{ is_array($current) ? implode("\n", $current) : $current }}</textarea>
            @break

        @default
            <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
                value="{{ $current }}" placeholder="{{ $placeholder }}"
                {{ $attributes->only(['min', 'max', 'step', 'accept', 'autocomplete']) }} @required($required)>
    @endswitch

    @if ($hint)
        <p class="adm-hint">{{ $hint }}</p>
    @endif

    @error(str_replace(['[', ']'], ['.', ''], $name))
        <p class="adm-error">{{ $message }}</p>
    @enderror
</div>
