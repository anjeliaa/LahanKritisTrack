<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data awal yang dibutuhkan sistem: akun & data spasial lahan (nyata).
     * Untuk contoh kegiatan + monitoring jalankan: php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LahanSeeder::class,
        ]);
    }
}
