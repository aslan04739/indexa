<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('app.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Email ou mot de passe incorrect.')])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('app.dashboard'));
    }

    public function showRegister(Request $request): View
    {
        return view('app.auth.register', ['role' => $request->query('role') === 'publisher' ? 'publisher' : 'buyer']);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in([User::BUYER, User::PUBLISHER])],
            'company' => ['nullable', 'string', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create($data + ['locale' => app()->getLocale()]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->isPublisher() ? 'app.sites.create' : 'app.catalog');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function locale(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => ['required', Rule::in(array_keys(config('indexa.locales')))]])['locale'];

        $request->session()->put('locale', $locale);
        $request->user()?->update(['locale' => $locale]);

        return back();
    }
}
