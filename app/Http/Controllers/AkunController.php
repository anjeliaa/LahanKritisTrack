<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AkunController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'role'     => ['required', 'in:admin,petugas'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
        ]);

        User::create($data);

        return $this->ok($request, 'Akun pengguna disimpan.');
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

        return $this->ok($request, 'Akun pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id_user === $request->user()->id_user) {
            throw ValidationException::withMessages(['akun' => 'Anda tidak dapat menghapus akun yang sedang Anda gunakan.']);
        }

        // kegiatan & monitoring yang pernah dicatat tetap tersimpan (id_user menjadi kosong)
        $user->delete();

        return $this->ok($request, 'Akun pengguna dihapus.');
    }
}
