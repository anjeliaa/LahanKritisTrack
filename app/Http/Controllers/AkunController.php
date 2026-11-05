<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Ui;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AkunController extends InternalController
{
    /** Halaman Tambah Akun. */
    public function create()
    {
        return $this->halaman('akun.form', ['item' => null]);
    }

    /** Halaman Ubah Akun. */
    public function edit(User $user)
    {
        return $this->halaman('akun.form', ['item' => $user]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'role'     => ['required', 'in:admin,petugas'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
        ]);

        User::create($data);

        return $this->kembaliKeDaftar($request, 'Akun pengguna disimpan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'role'     => ['required', 'in:admin,petugas'],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
        ]);

        // cegah admin menurunkan role dirinya sendiri (sistem bisa tanpa admin)
        if ($user->id_user === $request->user()->id_user && $data['role'] !== 'admin') {
            throw ValidationException::withMessages(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']); // kosong = tidak diubah
        }

        $user->update($data);

        return $this->kembaliKeDaftar($request, 'Akun pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id_user === $request->user()->id_user) {
            throw ValidationException::withMessages(['akun' => 'Anda tidak dapat menghapus akun yang sedang Anda gunakan.']);
        }

        // kegiatan & monitoring yang pernah dicatat tetap tersimpan (id_user menjadi kosong)
        $user->delete();

        // Hapus lewat popup (AJAX)
        return $this->ok($request, 'Akun pengguna dihapus.');
    }

    /** Menyusun halaman dengan kerangka internal (khusus Admin, dijaga oleh route). */
    private function halaman(string $view, array $extra)
    {
        $this->role = 'admin';

        $users = User::all();

        return $this->render($view, 'akun', 'admin.akun', [
            Ui::tile('Total Akun', $users->count(), 'Terdaftar', 'white'),
            Ui::tile('Admin', $users->where('role', 'admin')->count(), 'Akses penuh', 'amber'),
            Ui::tile('Petugas', $users->where('role', 'petugas')->count(), 'Lapangan', 'green'),
        ], $extra + ['kegiatan' => collect()]);
    }

    /** Setelah simpan: kembali ke daftar akun. */
    private function kembaliKeDaftar(Request $request, string $pesan)
    {
        $request->session()->flash('success', $pesan);

        return $request->expectsJson()
            ? response()->json(['message' => $pesan])
            : redirect()->route('admin.akun');
    }
}