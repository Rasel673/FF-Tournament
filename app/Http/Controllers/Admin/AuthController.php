<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $login = trim((string) ($request->input('login') ?? $request->input('email')));
        $password = (string) $request->input('password');

        if (empty($login) || empty($password)) {
            return back()->withErrors([
                'login' => 'Email or phone number and password are required.',
                'email' => 'Email or phone number and password are required.',
            ])->withInput($request->only('login', 'email'));
        }

        // Find user by email or phone
        $user = \App\Models\User::where(function ($q) use ($login) {
            $q->where('email', $login)
              ->orWhere('phone', $login);
        })->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors([
                'login' => 'Invalid credentials.',
                'email' => 'Invalid credentials.',
            ])->withInput($request->only('login', 'email'));
        }

        if ($user->is_blocked) {
            return back()->withErrors([
                'login' => 'Your account has been suspended. Please contact admin.',
                'email' => 'Your account has been suspended. Please contact admin.',
            ])->withInput($request->only('login', 'email'));
        }

        if (! $user->is_admin && ! $user->is_moderator) {
            return back()->withErrors([
                'login' => 'You do not have permission to access the admin portal.',
                'email' => 'You do not have permission to access the admin portal.',
            ])->withInput($request->only('login', 'email'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('admin.tournaments.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Auth::user();

        if (! Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $admin->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
