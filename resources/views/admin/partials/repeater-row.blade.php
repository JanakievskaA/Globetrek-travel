{{--
    One row of a repeater, used both for saved rows and for the <template> the
    "Add" button clones ($index is then the literal __index__, which admin.js
    rewrites to the real position).
--}}
<div class="adm-repeater__row" data-repeater-row>
    <div class="adm-repeater__head">
        <span class="adm-repeater__label">
            {{ $rowLabel }} <span data-repeater-number>{{ is_numeric($index) ? $index + 1 : '' }}</span>
        </span>
        <div class="adm-repeater__tools">
            <button type="button" class="adm-repeater__tool" data-repeater-up aria-label="Move up">↑</button>
            <button type="button" class="adm-repeater__tool" data-repeater-down aria-label="Move down">↓</button>
            <button type="button" class="adm-repeater__tool adm-repeater__tool--danger" data-repeater-remove
                aria-label="Remove">✕</button>
        </div>
    </div>

    <div class="adm-repeater__body">
        <div class="adm-form-grid">
            @foreach ($fields as $sub => $subSpec)
                <x-admin.dynamic-field :name="$prefix.'['.$index.']['.$sub.']'" :spec="$subSpec"
                    :value="$row[$sub] ?? null" :destinations="$destinations ?? []" />
            @endforeach
        </div>
    </div>
</div>
