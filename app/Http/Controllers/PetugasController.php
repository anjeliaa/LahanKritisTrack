<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\Catalog;
use App\Support\Fmt;
use App\Support\Ui;

class PetugasController extends InternalController
{
    protected string $role = 'petugas';

    /** Peta menampilkan SEMUA kegiatan (informasi sebaran); pengelolaan dibatasi di halaman Kegiatan. */
    public function peta()
    {
        $kegiatan = Catalog::kegiatan();

        // petak kritis yang BELUM selesai direhabilitasi
        $kritisTinggi = DataLahan::select('id_lahan', 'kritis')->denganRehabilitasi()->get()
            ->filter(fn ($l) => in_array($l->kritis, [config('lahan.kritis.kritis'), config('lahan.kritis.sangat')], true) && ! $l->sudah_direhabilitasi)
            ->count();

        return $this->render(
            'petugas.peta',
            'peta',
            'petugas.peta',
            [
                Ui::tile('Petak Lahan', DataLahan::count(), 'Terdata', 'white'),
                Ui::tile('Total Luas', Fmt::ha(DataLahan::sum('luas_ha')), 'Hektare', 'amber'),
                Ui::tile('Kritis', $kritisTinggi, 'Petak Lahan (kritis)', 'red'),
            ],
            ['kegiatan' => $kegiatan]
        );
    }

    /** Hanya kegiatan yang menjadi tanggung jawab petugas yang login. */
    public function kegiatan()
    {
        $kegiatan = Catalog::kegiatan(userId: auth()->id());

        [$total, $berjalan, $selesai] = $this->hitungStatus($kegiatan);

        return $this->render(
            'petugas.kegiatan',
            'kegiatan',
            'petugas.kegiatan',
            [
                Ui::tile('Total Kegiatan', $total, 'Tanggung jawab Anda', 'white'),
                Ui::tile('Berjalan', $berjalan, 'Perlu update', 'green'),
                Ui::tile('Selesai', $selesai, 'Tuntas', 'blue'),
            ],
            ['kegiatan' => $kegiatan]
        );
    }

    /** Riwayat monitoring pada kegiatan yang dikelola petugas (termasuk yang ia catat sendiri). */
    public function monitoring()
    {
        $kegiatan = Catalog::kegiatan(userId: auth()->id());
        $monitoring = Catalog::monitoring(auth()->user());

        return $this->render(
            'petugas.monitoring',
            'monitoring',
            'petugas.monitoring',
            [
                Ui::tile('Total Sesi', $monitoring->count(), 'Pemantauan', 'white'),
                Ui::tile('Rata-rata Progres', round($kegiatan->avg('progres') ?? 0).'%', 'Kegiatan Anda', 'green'),
                Ui::tile('Terpantau', $monitoring->pluck('id_kegiatan')->unique()->count(), 'Kegiatan', 'amber'),
            ],
            [
                'kegiatan'   => $kegiatan,
                'monitoring' => $monitoring,
                'monitoring_ringkas' => Catalog::monitoringRingkas($monitoring),
            ]
        );
    }
}
