@props([
    'destinations' => collect(),
    'categories' => collect(),
    'tone' => 'dark',
])

@php
    // The hero renders one of these per slide, so the ids have to be unique
    // per instance or every label would point at the first slide's fields.
    $uid = uniqid('gt-s-');
@endphp

<form action="{{ route('tours.index') }}" method="GET" class="gt-search {{ $tone === 'dark' ? 'gt-search--dark' : '' }}">
    <div class="gt-search__group">
        <label for="{{ $uid }}-destination">Where to</label>
        <select name="destination" id="{{ $uid }}-destination">
            <option value="">Anywhere</option>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->slug }}">{{ $destination->full_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="gt-search__group">
        <label for="{{ $uid }}-date">When</label>
        <input type="date" name="date" id="{{ $uid }}-date" min="{{ now()->toDateString() }}"
            data-empty-label="Any date">
    </div>

    <div class="gt-search__group">
        <label for="{{ $uid }}-category">Tour type</label>
        <select name="category" id="{{ $uid }}-category">
            <option value="">Any type</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="gt-search__group">
        <label for="{{ $uid }}-people">Travellers</label>
        <select name="people" id="{{ $uid }}-people" data-no-filter>
            <option value="">Any group size</option>
            @for ($i = 1; $i <= 10; $i++)
                <option value="{{ $i }}">{{ $i }} {{ Str::plural('traveller', $i) }}</option>
            @endfor
        </select>
    </div>

    <button type="submit" class="gt-search__submit" aria-label="Search tours">
        <span class="icon icon-search"></span>
        <span class="gt-search__submit-text">Search</span>
    </button>
</form>
