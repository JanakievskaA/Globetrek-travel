<x-layouts.admin title="Users">

    <x-admin.page-header title="Users" :subtitle="number_format($users->total()).' matching accounts'">
        <a href="{{ route('admin.users.create') }}" class="adm-btn">+ New user</a>
    </x-admin.page-header>

    <div class="adm-stats">
        <x-admin.stat-card label="All accounts" value="{{ number_format($totals['all']) }}" icon="icon-Profile" tone="info" />
        <x-admin.stat-card label="Customers" value="{{ number_format($totals['customers']) }}" icon="icon-user" tone="brand" />
        <x-admin.stat-card label="Staff" value="{{ $totals['staff'] }}" icon="icon-check" tone="ok" />
        <x-admin.stat-card label="Suspended" value="{{ $totals['suspended'] }}" icon="icon-X"
            :tone="$totals['suspended'] > 0 ? 'danger' : 'ok'" />
    </div>

    <x-admin.data-table :paginator="$users" empty="No users match these filters."
        :headers="['User', 'Role', 'Country', 'Bookings', 'Reviews', 'Joined', 'Status', ['label' => 'Actions', 'align' => 'right']]">

        <x-slot:filters>
            <form method="GET" action="{{ route('admin.users.index') }}" data-auto-filter class="adm-filters"
                style="border:0;padding:0;background:none;width:100%">
                <input type="search" name="q" value="{{ request('q') }}"
                    placeholder="Name, email or country…" class="adm-grow">
                <select name="role">
                    <option value="">Any role</option>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status">
                    <option value="">Any status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                </select>
                @if (request()->hasAny(['q', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="adm-btn adm-btn--ghost">Reset</a>
                @endif
            </form>
        </x-slot:filters>

        @foreach ($users as $user)
            <tr>
                <td>
                    <div class="adm-table__media">
                        <img src="{{ $user->avatar_url }}" alt="" class="adm-table__thumb adm-table__thumb--round">
                        <div>
                            <a href="{{ route('admin.users.show', $user) }}" class="adm-table__title">
                                {{ $user->name }}
                            </a>
                            <div class="adm-table__sub">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td><x-ui.badge :tone="$user->role->badge()">{{ $user->role->label() }}</x-ui.badge></td>
                <td>{{ $user->country ?? '—' }}</td>
                <td>{{ $user->bookings_count }}</td>
                <td>{{ $user->reviews_count }}</td>
                <td>{{ $user->created_at->format('M Y') }}</td>
                <td>
                    <x-ui.badge :tone="$user->status === 'active' ? 'success' : 'danger'">
                        {{ ucfirst($user->status) }}
                    </x-ui.badge>
                </td>
                <td>
                    <x-admin.row-actions :edit="route('admin.users.edit', $user)"
                        :destroy="route('admin.users.destroy', $user)"
                        :confirm="'Delete the account for '.$user->name.'?'">
                        <a href="{{ route('admin.users.show', $user) }}" class="adm-icon-btn" title="Profile">
                            <i class="icon icon-Search"></i>
                        </a>
                    </x-admin.row-actions>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
</x-layouts.admin>
