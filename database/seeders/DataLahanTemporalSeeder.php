<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Riwayat kekritisan per petak untuk tahun-tahun sebelum tahun data utama.
 *
 * Data tahun-tahun lama belum tersedia dari sumber resmi, sehingga dibentuk dari
 * salinan data tahun utama dengan nilai yang diubah agar perubahannya dapat dilihat
 * pada peta. Hasilnya deterministik (berdasarkan id petak), jadi seeder aman dijalankan ulang.
 *
 * Aturannya: sebagian petak berganti kelas SATU kali pada tahun tertentu (memburuk atau membaik
 * menuju kondisi tahun utama), dan luas tiap petak bergeser sedikit per tahun.
 */
class DataLahanTemporalSeeder extends Seeder
{
    public const TAHUN_AWAL = 2020;

    public function run(): void
    {
        $tahunUtama = (int) config('lahan.tahun_data', 2026);
        $label = array_values(config('lahan.kritis'));   // urut dari paling ringan
        $maks = 3;                                       // data hanya memuat empat kelas pertama

        $baris = [];
        $now = now();

        foreach (DB::table('data_lahan')->orderBy('id_lahan')->get(['id_lahan', 'kritis', 'luas_ha']) as $l) {
            $lv = array_search($l->kritis, $label, true);
            if ($lv === false) {
                continue;
            }

            // arah perubahan menuju tahun utama: +1 memburuk, -1 membaik, 0 tetap
            $arah = [0 => 1, 1 => 1, 2 => -1, 3 => -1][crc32("arah-{$l->id_lahan}") % 8] ?? 0;
            $awal = $lv - $arah;
            if ($awal < 0 || $awal > $maks) {
                $arah = 0;
                $awal = $lv;
            }
            // kelas berganti pada tahun ini (sebelumnya masih kelas $awal)
            $tahunUbah = self::TAHUN_AWAL + 1 + (crc32("thn-{$l->id_lahan}") % ($tahunUtama - self::TAHUN_AWAL));
            // pergeseran luas per tahun: -1,5% sampai +1,5%
            $geser = ((crc32("lua-{$l->id_lahan}") % 7) - 3) / 200;

            for ($t = self::TAHUN_AWAL; $t < $tahunUtama; $t++) {
                $baris[] = [
                    'id_lahan'   => $l->id_lahan,
                    'tahun'      => $t,
                    'kritis'     => $label[$t < $tahunUbah ? $awal : $lv],
                    'luas_ha'    => max(0, round($l->luas_ha * (1 + $geser * ($t - $tahunUtama)), 2)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('data_lahan_temporal')->truncate();
        foreach (array_chunk($baris, 400) as $paket) {
            DB::table('data_lahan_temporal')->insert($paket);
        }

        $this->command?->info('Riwayat kekritisan ' . self::TAHUN_AWAL . '–' . ($tahunUtama - 1) . ' dimuat: ' . count($baris) . ' baris.');
    }
}
