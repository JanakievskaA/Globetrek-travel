{{--
    Contact; $sections carries one PageSection per block, already merged with
    the defaults in App\Support\PageSections. Hidden sections drop out here.
--}}
@php
    $hero = $sections['contact_hero'];
    $intro = $sections['contact_intro'];
    $channels = $sections['contact_channels'];
    $form = $sections['contact_form'];
@endphp

<x-layouts.app title="Contact" description="Talk to a GlobeTrek travel specialist.">

    @if ($hero->is_visible)
        <x-ui.page-title :title="$hero->heading" :image="$hero->value('image')" :breadcrumbs="['Contact' => null]" />
    @endif

    <div class="flat-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    @if ($intro->is_visible)
                        <div class="gt-prose">
                            <div class="h3 mb-4">{{ $intro->heading }}</div>
                            @foreach ($paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if ($channels->is_visible)
                        <div class="gt-stack mt-6">
                            @foreach ($channels->rows('items') as $channel)
                                <div class="gt-info-item">
                                    <div class="gt-info-item__icon">
                                        @if (! empty($channel['icon']))
                                            <img src="{{ asset($channel['icon']) }}" alt="">
                                        @endif
                                    </div>
                                    <div>
                                        <div class="gt-info-item__label">{{ $channel['label'] ?? '' }}</div>
                                        <div class="gt-info-item__value">
                                            @if (! empty($channel['href']))
                                                <a href="{{ $channel['href'] }}">{{ $channel['value'] ?? '' }}</a>
                                            @else
                                                {{ $channel['value'] ?? '' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-lg-7 mt-6 mt-lg-0">
                    <div class="gt-card">
                        <form action="{{ route('contact.store') }}" method="POST" class="gt-stack">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="gt-field mb-4">
                                        <label for="ct-name">Name *</label>
                                        <input type="text" id="ct-name" name="name" required value="{{ old('name') }}">
                                        @error('name') <p class="gt-form-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="gt-field mb-4">
                                        <label for="ct-email">Email *</label>
                                        <input type="email" id="ct-email" name="email" required value="{{ old('email') }}">
                                        @error('email') <p class="gt-form-error">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="gt-field">
                                <label for="ct-subject">Subject</label>
                                <input type="text" id="ct-subject" name="subject" value="{{ old('subject') }}"
                                    placeholder="{{ $form->value('subject_placeholder') }}">
                            </div>

                            <div class="gt-field">
                                <label for="ct-message">Message *</label>
                                <textarea id="ct-message" name="message" rows="6" required
                                    placeholder="{{ $form->value('message_placeholder') }}">{{ old('message') }}</textarea>
                                @error('message') <p class="gt-form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <button type="submit" class="tf-btn primary hover-1">
                                    <span>{{ $form->value('button_label') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
