<?php

namespace Database\Seeders;

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;
use App\Models\Monitoring;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * OPSIONAL - contoh kegiatan & monitoring agar seluruh halaman langsung terisi saat uji coba.
 * Jalankan: php artisan db:seed --class=DemoDataSeeder   (tidak dipanggil oleh DatabaseSeeder)
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $fikhri = User::where('username', 'fikhri.petugas')->first();
        $habiby = User::where('username', 'habiby.petugas')->first();

        // kecamatan_id => petak terluas pada kecamatan tsb. (kegiatan harus menempel pada satu petak lahan)
        $petak = fn (int $kec) => DataLahan::where('kecamatan_id', $kec)->orderByDesc('luas_ha')->value('id_lahan');

        $kegiatan = [
            ['kec' => 7, 'nama' => 'Penanaman Vegetasi Bantaran Sungai Air Bengkulu', 'jenis' => 'Penanaman Vegetasi', 'pj' => 'Kelompok Tani Maju Jaya', 'target' => 12.0, 'mulai' => '2026-03-05', 'selesai' => '2026-12-15', 'status' => 'Berjalan', 'pub' => true,  'user' => $habiby,
             'mon' => ['2026-03-05', 10, 'Membaik', 'Sosialisasi masyarakat sempadan sungai selesai diselenggarakan.']],
            ['kec' => 2, 'nama' => 'Restorasi Mangrove Pesisir Pulau Baai', 'jenis' => 'Restorasi Mangrove', 'pj' => 'KTH Pesisir Baai', 'target' => 15.5, 'mulai' => '2026-02-18', 'selesai' => '2026-11-20', 'status' => 'Berjalan', 'pub' => true, 'user' => $fikhri,
             'mon' => ['2026-02-18', 40, 'Membaik', 'Pembibitan mangrove jenis Rhizophora sp. mencapai 5.000 polybag.']],
            ['kec' => 8, 'nama' => 'Reboisasi Pohon Mahoni & Akasia DAS Air Bengkulu', 'jenis' => 'Reboisasi Hutan', 'pj' => 'CV Hijau Lestari', 'target' => 25.0, 'mulai' => '2026-02-10', 'selesai' => '2026-10-30', 'status' => 'Berjalan', 'pub' => true, 'user' => $habiby,
             'mon' => ['2026-02-10', 65, 'Membaik', 'Penanaman 1.200 bibit mahoni selesai. Tingkat kelangsungan hidup 82%.']],
            ['kec' => 1, 'nama' => 'Program Agroforestry Tanaman Buah Rakyat', 'jenis' => 'Agroforestry', 'pj' => 'Kelompok Tani Sejahtera', 'target' => 18.0, 'mulai' => '2025-12-20', 'selesai' => '2026-05-20', 'status' => 'Selesai', 'pub' => true, 'user' => $habiby,
             'mon' => ['2026-05-20', 100, 'Membaik', 'Seluruh target tanaman alpukat & durian telah tumbuh stabil.']],
            // Teluk Segara belum punya petak pada data spasial, sehingga contoh ini ditempatkan di Gading Cempaka.
            ['kec' => 3, 'nama' => 'Sosialisasi Konservasi Lahan Kritis Sempadan', 'jenis' => 'Sosialisasi', 'pj' => 'Dinas LHK Kota Bengkulu', 'target' => 8.0, 'mulai' => '2026-04-15', 'selesai' => '2026-05-30', 'status' => 'Rencana', 'pub' => false, 'user' => $fikhri,
             'mon' => null],
        ];

        foreach ($kegiatan as $k) {
            $idLahan = $petak($k['kec']);
            if (! $idLahan) {
                continue;
            }

            $keg = KegiatanRehabilitasi::firstOrCreate(
                ['nama_kegiatan' => $k['nama']],
                [
                    'id_lahan'         => $idLahan,
                    'id_user'          => $k['user']?->id_user,
                    'penanggung_jawab' => $k['pj'],
                    'jenis_kegiatan'   => $k['jenis'],
                    'target_luas'      => $k['target'],
                    'tanggal_mulai'    => $k['mulai'],
                    'tanggal_selesai'  => $k['selesai'],
                    'status'           => $k['status'],
                    // kegiatan Selesai: tanggal status = tanggal penyelesaian (tanggal monitoring 100%)
                    'tanggal_status'   => ($k['status'] === 'Selesai' && $k['mon']) ? $k['mon'][0] : $k['mulai'],
                    'is_published'     => $k['pub'],
                ]
            );

            if ($k['mon'] && $keg->wasRecentlyCreated) {
                [$tgl, $progres, $kondisi, $catatan] = $k['mon'];
                Monitoring::create([
                    'id_kegiatan'        => $keg->id_kegiatan,
                    'id_user'            => $k['user']?->id_user,
                    'tanggal_monitoring' => $tgl,
                    'progres_persen'     => $progres,
                    'kondisi_lahan'      => $kondisi,
                    'catatan'            => $catatan,
                ]);
            }
        }
    }
}
