@php $editing = $category->exists; @endphp

<x-layouts.admin :title="$editing ? 'Edit category' : 'New category'">

    <x-admin.page-header :title="$editing ? $category->name : 'Create a category'"
        subtitle="Categories double as the tour-type filter on the listing page.">
        <a href="{{ route('admin.categories.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
    </x-admin.page-header>

    <form action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
        method="POST">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="adm-panel">
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="name" :value="$category->name" span="6" required />
                    <x-admin.field name="slug" :value="$category->slug" span="6"
                        hint="Leave blank to generate from the name." />

                    <x-admin.field name="description" type="textarea" rows="3"
                        :value="$category->description" span="12" />

                    <x-admin.image-field name="icon" label="Icon" :value="$category->icon" span="6"
                        hint="The small symbol in the “Types of tours” row." />
                    <x-admin.image-field name="image" label="Image" :value="$category->image" span="6"
                        hint="Used wherever the category appears as a card." />

                    <x-admin.field name="sort_order" label="Sort order" type="number" min="0"
                        :value="$category->sort_order ?? 0" span="4" />
                    <x-admin.field name="is_featured" label="Show on homepage" type="checkbox"
                        :value="$category->is_featured" span="4" />
                    <x-admin.field name="is_active" label="Visible on the site" type="checkbox"
                        :value="$category->is_active" span="4" />
                </div>
            </div>
            <div class="adm-form-actions">
                <a href="{{ route('admin.categories.index') }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">{{ $editing ? 'Save changes' : 'Create category' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
