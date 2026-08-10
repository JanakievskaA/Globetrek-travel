@props(['faqs' => []])

<div data-accordion>
    @foreach ($faqs as $index => $faq)
        <div class="gt-faq__item {{ $index === 0 ? 'is-open' : '' }}" data-accordion-item>
            <button type="button" class="gt-faq__trigger" data-accordion-trigger>
                <span>{{ $faq['question'] }}</span>
                <i class="icon icon-CaretDown gt-itinerary__caret"></i>
            </button>
            <div class="gt-faq__body">{{ $faq['answer'] }}</div>
        </div>
    @endforeach
</div>
