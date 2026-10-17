<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show(Request $request)
    {
        if ($user = $request->user()) {
            return redirect()->route($this->home($user->role));
        }

        return view('pages.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $key = Str::lower($data['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $menit = ceil(RateLimiter::availableIn($key) / 60);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$menit} menit.",
            ]);
        }

        if (! Auth::attempt(['username' => $data['username'], 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 300);

            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route($this->home($request->user()->role)));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function home(string $role): string
    {
        return $role === 'admin' ? 'admin.dashboard' : 'petugas.peta';
    }
}
