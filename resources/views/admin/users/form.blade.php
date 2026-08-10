@php $editing = $user->exists; @endphp

<x-layouts.admin :title="$editing ? 'Edit user' : 'New user'">

    <x-admin.page-header :title="$editing ? $user->name : 'Create a user'"
        :subtitle="$editing ? $user->email : 'Add a customer or staff account.'">
        <a href="{{ route('admin.users.index') }}" class="adm-btn adm-btn--ghost">Back to list</a>
    </x-admin.page-header>

    <form action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="adm-panel">
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="name" label="Full name" :value="$user->name" span="6" required />
                    <x-admin.field name="email" type="email" :value="$user->email" span="6" required />

                    <x-admin.field name="role" type="select" :value="$user->role?->value"
                        :options="$roles" span="4" required />
                    <x-admin.field name="status" type="select" :value="$user->status"
                        :options="['active' => 'Active', 'suspended' => 'Suspended']" span="4" required />
                    <x-admin.field name="country" :value="$user->country" span="4" />

                    <x-admin.field name="phone" :value="$user->phone" span="6" />
                    <x-admin.field name="password" type="password" span="6"
                        :required="! $editing" autocomplete="new-password"
                        :hint="$editing ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.'" />
                </div>
            </div>
            <div class="adm-form-actions">
                <a href="{{ route('admin.users.index') }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">{{ $editing ? 'Save changes' : 'Create user' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
