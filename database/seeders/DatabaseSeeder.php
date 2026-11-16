<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seluruh seeder data awal sistem.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LahanSeeder::class,
            DataLahanTemporalSeeder::class,
        ]);
    }
}
