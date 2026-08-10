@php $editing = $destination->exists; @endphp

<x-layouts.admin :title="$editing ? 'Edit destination' : 'New destination'">

    <x-admin.page-header :title="$editing ? $destination->name : 'Create a destination'"
        :subtitle="$editing ? $destination->country : 'Add a new place to the catalogue.'">
        <a href="{{ route('admin.destinations.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
    </x-admin.page-header>

    <form action="{{ $editing ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}"
        method="POST">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Details</div></div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="name" :value="$destination->name" span="4" required />
                    <x-admin.field name="country" :value="$destination->country" span="4" required />
                    <x-admin.field name="continent" :value="$destination->continent" span="4" />

                    <x-admin.field name="slug" :value="$destination->slug" span="6"
                        hint="Leave blank to generate automatically." />
                    <x-admin.field name="sort_order" label="Sort order" type="number" min="0"
                        :value="$destination->sort_order ?? 0" span="6" />

                    <x-admin.field name="summary" type="textarea" rows="2" :value="$destination->summary"
                        span="12" required placeholder="One sentence for cards and search results." />

                    <x-admin.field name="description" type="textarea" rows="7"
                        :value="$destination->description" span="12" />
                </div>
            </div>
        </div>

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Imagery &amp; travel facts</div></div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.image-field name="image" label="Card image" :value="$destination->image" span="6"
                        required hint="Shown on destination cards across the site." />
                    <x-admin.image-field name="hero_image" label="Hero image" :value="$destination->hero_image"
                        span="6" hint="The wide banner on the destination page. Falls back to the card image." />

                    <x-admin.field name="best_season" label="Best season" :value="$destination->best_season" span="6" />
                    <x-admin.field name="currency" :value="$destination->currency" span="6" />
                    <x-admin.field name="language" :value="$destination->language" span="6" />
                    <x-admin.field name="timezone" :value="$destination->timezone" span="6" />

                    <x-admin.field name="latitude" type="number" step="0.0000001"
                        :value="$destination->latitude" span="6" />
                    <x-admin.field name="longitude" type="number" step="0.0000001"
                        :value="$destination->longitude" span="6" />

                    <x-admin.field name="is_featured" label="Feature on homepage" type="checkbox"
                        :value="$destination->is_featured" span="6" />
                    <x-admin.field name="is_active" label="Visible on the site" type="checkbox"
                        :value="$destination->is_active" span="6" />
                </div>
            </div>
        </div>

        <div class="adm-panel">
            <div class="adm-form-actions">
                <a href="{{ route('admin.destinations.index') }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">{{ $editing ? 'Save changes' : 'Create destination' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
