<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\Ui;
use Illuminate\Support\Collection;

/** Basis halaman internal (Admin & Petugas): menyiapkan data bersama untuk layout, modal, dan peta. */
abstract class InternalController extends Controller
{
    protected string $role;

    /**
     * @param  array  $stats  ubin statistik pada header halaman
     * @param  array  $data   data khusus halaman; `kegiatan` (Collection) boleh diisi agar tidak query ulang
     */
    protected function render(string $view, string $active, string $headerKey, array $stats, array $data = [])
    {
        /** @var Collection $kegiatan */
        $kegiatan = $data['kegiatan'] ?? Catalog::kegiatan();
        $kecamatan = Catalog::kecamatanSemua();   // pilihan filter: semua kecamatan
        $kecamatanData = Catalog::kecamatan();    // form kegiatan: hanya kecamatan yang punya petak

        $app = Catalog::app($kecamatan, $kegiatan, $data['app_extra'] ?? []);
        unset($data['app_extra']);

        return view($view, array_merge([
            'role'        => $this->role,
            'tabs'        => Ui::tabs($this->role),
            'active'      => $active,
            'ph'          => Ui::header($headerKey, $stats),
            'kecamatan'   => $kecamatan,
            'kecamatan_data' => $kecamatanData,
            'kegiatan'    => $kegiatan,
            'petak'       => Catalog::petak(),
            'status_opts' => config('lahan.status'),
            'kondisi_opts' => config('lahan.kondisi'),
            'kritis_opts' => Catalog::kritisOpts(),   // form ubah petak: semua kelas
            'kritis_ada'  => Catalog::kritisAda(),    // filter: hanya kelas yang ada pada data
            'app'         => $app,
        ], $data));
    }

    /** @return array{0:int,1:int,2:int,3:int} total, berjalan, selesai, rencana */
    protected function hitungStatus(Collection $kegiatan): array
    {
        return [
            $kegiatan->count(),
            $kegiatan->where('status', 'Berjalan')->count(),
            $kegiatan->where('status', 'Selesai')->count(),
            $kegiatan->where('status', 'Rencana')->count(),
        ];
    }
}
