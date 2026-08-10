<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect($this->destinationFor($request))
            ->with('success', 'Welcome back, '.str($request->user()->name)->before(' ').'.');
    }

    public function showRegister(): View
    {
        return view('pages.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            ...$data,
            'role' => UserRole::Customer,
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.bookings')
            ->with('success', 'Account created — welcome to GlobeTrek.');
    }

    /**
     * Where to land after signing in.
     *
     * Normally the page they were trying to reach, but a customer who happened
     * to open /admin first would otherwise be redirected straight into a 403 —
     * a remembered URL they were never allowed to visit is worse than none.
     */
    private function destinationFor(Request $request): string
    {
        $user = $request->user();
        $home = $user->isStaff() ? route('admin.dashboard') : route('account.bookings');

        $intended = $request->session()->pull('url.intended');

        if (! $intended) {
            return $home;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?: '';

        return $user->isStaff() || ! str_starts_with(ltrim($path, '/'), 'admin')
            ? $intended
            : $home;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }
}
