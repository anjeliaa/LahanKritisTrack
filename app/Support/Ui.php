<?php

namespace App\Support;

/**
 * Navigasi tab dan judul halaman.
 * Angka statistik dihitung oleh controller.
 */
class Ui
{
    public static function tabs(string $role): array
    {
        return [
            'admin' => [
                ['key' => 'dashboard', 'label' => 'Dashboard Statistik', 'route' => 'admin.dashboard', 'icon' => 'pie'],
                ['key' => 'akun', 'label' => 'Manajemen Akun Pengguna', 'route' => 'admin.akun', 'icon' => 'users'],
                ['key' => 'lahan', 'label' => 'Manajemen Data Lahan Kritis', 'route' => 'admin.lahan', 'icon' => 'pin'],
                ['key' => 'kegiatan', 'label' => 'Manajemen Kegiatan Rehabilitasi', 'route' => 'admin.kegiatan', 'icon' => 'sprout'],
                ['key' => 'monitoring', 'label' => 'Manajemen Kegiatan Monitoring', 'route' => 'admin.monitoring', 'icon' => 'clipcheck'],
                ['key' => 'laporan', 'label' => 'Laporan Kegiatan & Monitoring', 'route' => 'admin.laporan', 'icon' => 'filetext'],
            ],

            'petugas' => [
                ['key' => 'peta', 'label' => 'Lihat Peta Lahan Kritis', 'route' => 'petugas.peta', 'icon' => 'pin'],
                ['key' => 'kegiatan', 'label' => 'Manajemen Kegiatan Rehabilitasi', 'route' => 'petugas.kegiatan', 'icon' => 'sprout'],
                ['key' => 'monitoring', 'label' => 'Manajemen Kegiatan Monitoring', 'route' => 'petugas.monitoring', 'icon' => 'clipcheck'],
            ],
        ][$role];
    }

    public static function header(string $key, array $stats): array
    {
        $h = [
            'admin.dashboard' => ['pie', 'Dashboard Statistik & Pemantauan', 'Ringkasan kondisi lahan kritis, capaian rehabilitasi, dan aktivitas monitoring lapangan Kota Bengkulu.'],
            'admin.akun' => ['users', 'Manajemen Akun Pengguna', 'Kelola akun Admin dan Petugas lapangan yang memiliki akses ke sistem LahanKritisTrack.'],
            'admin.lahan' => ['pin', 'Manajemen Data Spasial Lahan Kritis', 'Kelola data luas dan tingkat kekritisan setiap petak lahan, termasuk impor GeoJSON.'],
            'admin.kegiatan' => ['sprout', 'Manajemen Kegiatan Rehabilitasi', 'Rencanakan, pantau, dan publikasikan program reboisasi serta rehabilitasi lahan kritis.'],
            'admin.monitoring' => ['camera', 'Manajemen Monitoring Lapangan', 'Tinjau riwayat pemantauan lapangan, dokumentasi foto, dan progres setiap kegiatan.'],
            'admin.laporan' => ['filetext', 'Laporan Kegiatan & Monitoring', 'Susun dan unduh laporan terpadu kegiatan rehabilitasi dan monitoring dalam format PDF atau Excel.'],

            'petugas.peta' => ['map', 'Peta Lahan Kritis Spasial', 'Telusuri sebaran lahan kritis per kecamatan dan tambahkan kegiatan langsung dari peta.'],
            'petugas.kegiatan' => ['sprout', 'Kegiatan Rehabilitasi Lahan', 'Perbarui status dan data kegiatan rehabilitasi yang menjadi tanggung jawab lapangan Anda.'],
            'petugas.monitoring' => ['camera', 'Monitoring Lapangan', 'Catat hasil pemantauan, unggah dokumentasi, dan perbarui progres kegiatan di lapangan.'],
            'profil' => ['users', 'Pengaturan Akun', 'Lihat informasi akun Anda dan ubah kata sandi.'],
        ][$key];

        return [
            'icon' => $h[0],
            'path' => 'lahankritistrack / ' . str_replace('.', ' / ', $key),
            'title' => $h[1],
            'desc' => $h[2],
            'stats' => $stats,
        ];
    }

    public static function tile(
        string $label,
        string|int|float $value,
        string $sub,
        string $tone = 'white'
    ): array {
        return [
            'label' => $label,
            'value' => (string) $value,
            'sub' => $sub,
            'tone' => $tone,
        ];
    }
}
