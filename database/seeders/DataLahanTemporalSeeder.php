<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataLahanTemporalSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'Tidak Kritis',
            'Potensial Kritis',
            'Agak Kritis',
            'Kritis',
            'Sangat Kritis',
        ];

        // Mengambil data asli dari tabel data_lahan
        $dataLahan = DB::table('data_lahan')->get();

        foreach ($dataLahan as $lahan) {

            // ====================================
            // DATA ASLI TAHUN 2026
            // ====================================

            DB::table('data_lahan_temporal')->updateOrInsert(
                [
                    'id_lahan' => $lahan->id_lahan,
                    'tahun' => 2026,
                    'data_source' => 'asli',
                ],
                [
                    'kritis' => $lahan->kritis,
                    'luas_ha' => $lahan->luas_ha,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // ====================================
            // DATA DUMMY TAHUN 2020-2025
            // ====================================

            $levelAwal = array_search(
                $lahan->kritis,
                $kategori
            );

            if ($levelAwal === false) {
                $levelAwal = 2;
            }

            for ($tahun = 2020; $tahun <= 2025; $tahun++) {

                // Simulasi perubahan kategori
                $angka = abs(
                    crc32($lahan->id_lahan . '-' . $tahun)
                ) % 5;

                if ($angka === 0) {
                    $perubahan = -1;
                } elseif ($angka === 4) {
                    $perubahan = 1;
                } else {
                    $perubahan = 0;
                }

                $levelDummy = max(
                    0,
                    min(4, $levelAwal + $perubahan)
                );

                $kategoriDummy = $kategori[$levelDummy];

                // Simulasi perubahan luas lahan
                $angkaLuas = abs(
                    crc32(
                        'luas-' .
                        $lahan->id_lahan .
                        '-' .
                        $tahun
                    )
                ) % 15;

                $persentase = ($angkaLuas - 7) / 100;

                $luasDummy = max(
                    0,
                    round(
                        $lahan->luas_ha * (1 + $persentase),
                        2
                    )
                );

                // Menyimpan data dummy
                DB::table('data_lahan_temporal')->updateOrInsert(
                    [
                        'id_lahan' => $lahan->id_lahan,
                        'tahun' => $tahun,
                        'data_source' => 'dummy',
                    ],
                    [
                        'kritis' => $kategoriDummy,
                        'luas_ha' => $luasDummy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info(
            'Data temporal asli dan dummy berhasil dimasukkan.'
        );
    }
}
