<?php

namespace Database\Seeders;

use App\Support\LahanImporter;
use Illuminate\Database\Seeder;

class LahanSeeder extends Seeder
{
    /** Memuat data spasial petak lahan kritis (BPDAS Ketahun) dari berkas GeoJSON ke tabel data_lahan. */
    public function run(): void
    {
        $r = LahanImporter::fromJson(file_get_contents(__DIR__.'/data/lahan_kritis.geojson'));

        $this->command?->info("Data lahan: {$r['created']} ditambahkan, {$r['updated']} diperbarui, {$r['skipped']} dilewati.");

        foreach ($r['errors'] as $e) {
            $this->command?->warn($e);
        }
    }
}
