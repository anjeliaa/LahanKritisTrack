<?php

namespace App\Http\Controllers;

use App\Support\Fmt;
use Illuminate\Http\Request;

/**
 * Pengaturan akun milik sendiri (Admin & Petugas), sesuai Class Diagram: User.lihatProfil() dan User.ubahPassword().
 * Nama, username, dan role hanya dapat diubah Admin lewat Manajemen Akun Pengguna.
 */
class ProfilController extends InternalController
{
    public function show(Request $request)
    {
        $this->role = $request->user()->role;
        $user = $request->user();

        return $this->render('pages.profil', 'profil', 'profil', [], [
            'kegiatan' => collect(),
            'akun'     => [
                'nama'     => $user->nama,
                'username' => $user->username,
                'role'     => config("lahan.role.{$user->role}"),
                'dibuat'   => Fmt::tanggal($user->created_at, 'd F Y'),
                'inisial'  => mb_strtoupper(mb_substr($user->nama, 0, 1)),
                'jml_kegiatan' => $user->role === 'petugas' ? $user->kegiatan()->count() : null,
            ],
        ]);
    }

    public function ubahPassword(Request $request)
    {
        $request->validate([
            'password_lama' => ['required', 'string', 'current_password'],
            'password_baru' => ['required', 'string', 'min:8', 'max:100', 'confirmed', 'different:password_lama'],
        ]);

        $request->user()->update(['password' => $request->input('password_baru')]);

        return $this->ok($request, 'Kata sandi berhasil diubah.');
    }
}
