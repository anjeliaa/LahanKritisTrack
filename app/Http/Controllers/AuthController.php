<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Autentikasi SEMENTARA (tahap frontend): tanpa database.
 * Email yang mengandung "petugas" => role petugas, selain itu => admin.
 * Ganti dengan Laravel Auth (Breeze/Fortify) saat backend dibuat.
 */
class AuthController extends Controller
{
    public function show(Request $request)
    {
        if ($role = $request->session()->get('role')) {
            return redirect()->route($role === 'admin' ? 'admin.dashboard' : 'petugas.peta');
        }

        return view('pages.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:3'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        return $this->enter($request, str_contains(strtolower($data['email']), 'petugas') ? 'petugas' : 'admin');
    }

    public function demo(Request $request, string $role)
    {
        abort_unless(in_array($role, ['admin', 'petugas'], true), 404);

        return $this->enter($request, $role);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('home');
    }

    private function enter(Request $request, string $role)
    {
        $request->session()->regenerate();
        $request->session()->put('role', $role);

        return redirect()->route($role === 'admin' ? 'admin.dashboard' : 'petugas.peta');
    }
}
