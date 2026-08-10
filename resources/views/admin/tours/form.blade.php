@php $editing = $tour->exists; @endphp

<x-layouts.admin :title="$editing ? 'Edit tour' : 'New tour'">

    <x-admin.page-header :title="$editing ? $tour->title : 'Create a tour'"
        :subtitle="$editing ? 'Last updated '.$tour->updated_at->diffForHumans() : 'Add a new departure to the catalogue.'">
        <a href="{{ route('admin.tours.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
        @if ($editing)
            <a href="{{ route('tours.show', $tour) }}" class="adm-btn adm-btn--ghost" target="_blank" rel="noopener">
                View on site
            </a>
        @endif
    </x-admin.page-header>

    <form action="{{ $editing ? route('admin.tours.update', $tour) : route('admin.tours.store') }}" method="POST">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Basics</div></div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="title" label="Tour title" :value="$tour->title" span="8" required />
                    <x-admin.field name="slug" :value="$tour->slug" span="4"
                        hint="Leave blank to generate from the title." />

                    <x-admin.field name="destination_id" label="Destination" type="select"
                        :value="$tour->destination_id" :options="$destinations" span="4" required />
                    <x-admin.field name="category_id" label="Category" type="select"
                        :value="$tour->category_id" :options="$categories" span="4" required />
                    <x-admin.field name="status" type="select" :value="$tour->status?->value"
                        :options="$statuses" span="4" required />

                    <x-admin.field name="summary" label="Short summary" type="textarea" rows="2"
                        :value="$tour->summary" span="12" required
                        placeholder="One or two sentences shown on cards and search results." />

                    <x-admin.field name="description" label="Full description" type="textarea" rows="7"
                        :value="$tour->description" span="12" required />

                    <x-admin.image-field name="image" label="Cover image" :value="$tour->image" span="8" required
                        hint="The main photo on cards and at the top of the tour page." />
                    <x-admin.field name="is_featured" label="Feature on homepage" type="checkbox"
                        :value="$tour->is_featured" span="4" />
                </div>
            </div>
        </div>

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Pricing &amp; logistics</div></div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="price" label="Price per adult ($)" type="number" step="0.01" min="0"
                        :value="$tour->price" span="3" required />
                    <x-admin.field name="sale_price" label="Sale price ($)" type="number" step="0.01" min="0"
                        :value="$tour->sale_price" span="3" hint="Leave blank for no discount." />
                    <x-admin.field name="group_size" label="Max group size" type="number" min="1"
                        :value="$tour->group_size ?? 12" span="3" required />
                    <x-admin.field name="min_age" label="Minimum age" type="number" min="0"
                        :value="$tour->min_age ?? 0" span="3" required />

                    <x-admin.field name="duration_days" label="Duration (days)" type="number" min="0"
                        :value="$tour->duration_days ?? 0" span="3" required
                        hint="Use 0 for same-day tours." />
                    <x-admin.field name="duration_nights" label="Nights" type="number" min="0"
                        :value="$tour->duration_nights ?? 0" span="3" required />
                    <x-admin.field name="duration_hours" label="Duration (hours)" type="number" min="1" max="24"
                        :value="$tour->duration_hours" span="3" hint="Only for same-day tours." />
                    <x-admin.field name="difficulty" type="select" :value="$tour->difficulty"
                        :options="$difficulties" span="3" required />

                    <x-admin.field name="departure_point" label="Departure point" :value="$tour->departure_point"
                        span="6" />
                    <x-admin.field name="contact_phone" label="Contact phone" :value="$tour->contact_phone"
                        span="6" />
                </div>
            </div>
        </div>

        <div class="adm-panel">
            <div class="adm-panel__head">
                <div class="adm-panel__title">Details</div>
                <span class="adm-hint">One item per line</span>
            </div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="highlights" type="list" rows="5" :value="$tour->highlights" span="6"
                        placeholder="Sunrise at the temple&#10;Walk the terraces with a local family" />
                    <x-admin.field name="languages" type="list" rows="5" :value="$tour->languages" span="6"
                        placeholder="English&#10;Spanish" />
                    <x-admin.field name="includes" label="What's included" type="list" rows="6"
                        :value="$tour->includes" span="6" />
                    <x-admin.field name="excludes" label="Not included" type="list" rows="6"
                        :value="$tour->excludes" span="6" />
                    <x-admin.field name="amenities" type="list" rows="4" :value="$tour->amenities" span="12"
                        hint="These power the “Included” filter on the tour list." />
                </div>
            </div>
        </div>

        @if ($editing && $tour->itineraries->isNotEmpty())
            <div class="adm-panel">
                <div class="adm-panel__head">
                    <div class="adm-panel__title">Itinerary</div>
                    <span class="adm-hint">{{ $tour->itineraries->count() }} steps</span>
                </div>
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead><tr><th>Step</th><th>Title</th><th>Duration</th><th>Meals</th><th>Stay</th></tr></thead>
                        <tbody>
                            @foreach ($tour->itineraries as $step)
                                <tr>
                                    <td>{{ $step->day }}</td>
                                    <td>
                                        <span class="adm-table__title">{{ $step->title }}</span>
                                        <div class="adm-table__sub adm-clamp-2">{{ $step->description }}</div>
                                    </td>
                                    <td>{{ $step->duration ?? '—' }}</td>
                                    <td>{{ $step->meals ?? '—' }}</td>
                                    <td>{{ $step->accommodation ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @php
            // Gallery rows post as images[n][path|alt] and replace the tour's
            // photos on save; see TourController::syncImages().
            $galleryFields = [
                'path' => ['type' => 'image', 'label' => 'Photo', 'span' => 8],
                'alt' => ['type' => 'text', 'label' => 'Description', 'span' => 4,
                    'hint' => 'Read aloud by screen readers and shown if the photo fails to load.'],
            ];
            $galleryRows = old('images', $editing
                ? $tour->images->map(fn ($image) => ['path' => $image->path, 'alt' => $image->alt])->all()
                : []);
        @endphp

        <div class="adm-panel" data-repeater data-repeater-max="12">
            <div class="adm-panel__head">
                <div class="adm-panel__title">Gallery</div>
                <button type="button" class="adm-btn adm-btn--ghost adm-btn--sm" data-repeater-add>+ Add photo</button>
            </div>

            <div class="adm-panel__body">
                <p class="adm-hint" style="margin-top:0">
                    Extra photos for the tour page, shown after the cover image.
                </p>

                <div class="adm-repeater" data-repeater-list>
                    @foreach ($galleryRows as $index => $row)
                        @include('admin.partials.repeater-row', [
                            'index' => $index,
                            'row' => $row,
                            'fields' => $galleryFields,
                            'prefix' => 'images',
                            'rowLabel' => 'Photo',
                        ])
                    @endforeach
                </div>

                <p class="adm-repeater__empty" data-repeater-empty @if (count($galleryRows)) hidden @endif>
                    No extra photos yet — use “Add photo”.
                </p>
            </div>

            <template data-repeater-template>
                @include('admin.partials.repeater-row', [
                    'index' => '__index__',
                    'row' => [],
                    'fields' => $galleryFields,
                    'prefix' => 'images',
                    'rowLabel' => 'Photo',
                ])
            </template>
        </div>

        <div class="adm-panel">
            <div class="adm-form-actions">
                <a href="{{ route('admin.tours.index') }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">{{ $editing ? 'Save changes' : 'Create tour' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
