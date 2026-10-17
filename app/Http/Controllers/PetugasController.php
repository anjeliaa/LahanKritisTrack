<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\Catalog;
use App\Support\Fmt;
use App\Support\Ui;

class PetugasController extends InternalController
{
    protected string $role = 'petugas';

    public function peta()
    {
        $kegiatan = Catalog::kegiatan();
        $kritisTinggi = DataLahan::whereIn('kritis', [config('lahan.kritis.kritis'), config('lahan.kritis.sangat')])->count();

        return $this->render('petugas.peta', 'peta', 'petugas.peta', [
            Ui::tile('Petak Lahan', DataLahan::count(), 'Terdata', 'white'),
            Ui::tile('Total Luas', Fmt::ha(DataLahan::sum('luas_ha')), 'Hektare', 'amber'),
            Ui::tile('Kritis', $kritisTinggi, 'Petak Lahan (kritis)', 'red'),
        ], ['kegiatan' => $kegiatan]);
    }

    public function kegiatan()
    {
        $kegiatan = Catalog::kegiatan();
        [$total, $berjalan, $selesai] = $this->hitungStatus($kegiatan);

        return $this->render('petugas.kegiatan', 'kegiatan', 'petugas.kegiatan', [
            Ui::tile('Total Kegiatan', $total, 'Program', 'white'),
            Ui::tile('Berjalan', $berjalan, 'Perlu update', 'green'),
            Ui::tile('Selesai', $selesai, 'Tuntas', 'blue'),
        ], ['kegiatan' => $kegiatan]);
    }

    public function monitoring()
    {
        $kegiatan = Catalog::kegiatan();
        $monitoring = Catalog::monitoring(auth()->user());

        return $this->render('petugas.monitoring', 'monitoring', 'petugas.monitoring', [
            Ui::tile('Total Sesi', $monitoring->count(), 'Pemantauan', 'white'),
            Ui::tile('Rata-rata Progres', round($kegiatan->avg('progres') ?? 0).'%', 'Semua kegiatan', 'green'),
            Ui::tile('Terpantau', $monitoring->pluck('id_kegiatan')->unique()->count(), 'Kegiatan', 'amber'),
        ], ['kegiatan' => $kegiatan, 'monitoring' => $monitoring]);
    }
}
