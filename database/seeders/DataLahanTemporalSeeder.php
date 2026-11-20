<?php

namespace Database\Seeders;

use App\Support\LahanImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Riwayat kekritisan per petak untuk tahun-tahun sebelum tahun data utama (visualisasi temporal pada peta).
 *
 * Datanya dibaca dari berkas GeoJSON per tahun di database/seeders/data/temporal/lahan_kritis_{tahun}.geojson
 * (bentuknya sama dengan lahan_kritis.geojson). Berkas-berkas itu adalah salinan data tahun utama dengan
 * nilai kelas kekritisan dan luas yang diubah, karena data tahun-tahun lama belum tersedia dari sumber resmi.
 * Untuk memakai data asli, cukup ganti isi berkasnya (properti kritis dan luas_ha) lalu jalankan seeder ini lagi.
 * Petak dicocokkan dengan data_lahan lewat pasangan (objectid, kecamatan).
 */
class DataLahanTemporalSeeder extends Seeder
{
    public function run(): void
    {
        $tahunUtama = (int) config('lahan.tahun_data', 2026);

        $peta = [];   // "objectid|kecamatan_id" => id_lahan
        foreach (DB::table('data_lahan')->get(['id_lahan', 'objectid', 'kecamatan_id']) as $l) {
            $peta[$l->objectid.'|'.$l->kecamatan_id] = $l->id_lahan;
        }

        $baris = [];
        $now = now();

        foreach (glob(__DIR__.'/data/temporal/lahan_kritis_*.geojson') ?: [] as $berkas) {
            if (! preg_match('/lahan_kritis_(\d{4})\.geojson$/', $berkas, $m) || (int) $m[1] >= $tahunUtama) {
                continue;
            }
            $tahun = (int) $m[1];
            $json = json_decode(file_get_contents($berkas), true);

            foreach ($json['features'] ?? [] as $f) {
                $p = array_change_key_case((array) ($f['properties'] ?? []), CASE_LOWER);
                $kec = LahanImporter::kecamatanId($p);
                $kritis = LahanImporter::kritisLabel($p['kritis'] ?? null);
                $id = $peta[($p['objectid'] ?? '').'|'.$kec] ?? null;

                if ($id === null || $kritis === null || ! is_numeric($p['luas_ha'] ?? null)) {
                    $this->command?->warn("{$tahun}: satu petak dilewati (tidak cocok dengan data_lahan).");
                    continue;
                }

                $baris[] = [
                    'id_lahan'   => $id,
                    'tahun'      => $tahun,
                    'kritis'     => $kritis,
                    'luas_ha'    => round((float) $p['luas_ha'], 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('data_lahan_temporal')->truncate();
        foreach (array_chunk($baris, 400) as $paket) {
            DB::table('data_lahan_temporal')->insert($paket);
        }

        $this->command?->info('Riwayat kekritisan dimuat dari GeoJSON: '.count($baris).' baris.');
    }
}
