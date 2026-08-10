<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount(['bookings', 'reviews'])
            ->search($request->string('q')->trim()->value() ?: null)
            ->when($request->input('role'), fn ($q, $r) => $q->where('role', $r))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::options(),
            'totals' => [
                'all' => User::count(),
                'customers' => User::where('role', UserRole::Customer)->count(),
                'staff' => User::whereIn('role', [UserRole::Admin, UserRole::Manager])->count(),
                'suspended' => User::where('status', 'suspended')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => UserRole::Customer, 'status' => 'active']),
            'roles' => UserRole::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($request->input('password'));

        $user = User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} has been added.");
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user->loadCount(['bookings', 'reviews']),
            'bookings' => $user->bookings()->with('tour')->latest()->take(10)->get(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => UserRole::options()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        // A blank field means "leave it alone" — drop the key entirely so the
        // existing hash is not overwritten with null.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete the account you are signed in with.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$name} was removed.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            // withoutTrashed: a removed account should not block its address forever.
            'email' => ['required', 'email', 'max:180',
                Rule::unique('users', 'email')->ignore($user?->id)->withoutTrashed()],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);
    }
}
