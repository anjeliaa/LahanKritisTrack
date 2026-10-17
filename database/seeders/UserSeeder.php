<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kata sandi awal. GANTI setelah login pertama (menu Manajemen Akun).
        $password = env('SEED_USER_PASSWORD', 'lahankritis123');

        $akun = [
            ['nama' => 'Anjelia Febiliyanti',  'username' => 'anjelia.admin',  'role' => 'admin'],
            ['nama' => 'R. Noor Fikhri',       'username' => 'fikhri.petugas', 'role' => 'petugas'],
            ['nama' => 'Habiby Irawan Halim',  'username' => 'habiby.petugas', 'role' => 'petugas'],
        ];

        foreach ($akun as $a) {
            User::updateOrCreate(['username' => $a['username']], $a + ['password' => $password]);
        }
    }
}
