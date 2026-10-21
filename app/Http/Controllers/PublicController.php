<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\Catalog;
use App\Support\Fmt;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function landing()
    {
        // Halaman publik HANYA menampilkan kegiatan yang ditandai "dipublikasikan".
        $publik = Catalog::kegiatan(publishedOnly: true, forPublic: true);
        $kecamatan = Catalog::kecamatanSemua();          // filter: semua kecamatan
        $jmlKecData = Catalog::kecamatan()->count();      // kecamatan yang memiliki data

        // "Luas lahan kritis" tidak lagi menghitung petak yang kegiatan rehabilitasinya (yang dipublikasikan) sudah Selesai
        $kritisHa = DataLahan::select('id_lahan', 'kritis', 'luas_ha')->denganRehabilitasi(true)->get()
            ->filter(fn ($l) => in_array($l->kritis, $this->labelKritis(), true) && ! $l->sudah_direhabilitasi)
            ->sum('luas_ha');
        $jmlKec = $jmlKecData;
        $target = $publik->sum('target');
        $real = $publik->sum('realisasi');
        $persen = $target > 0 ? round($real / $target * 100, 1) : 0;
        $bpdas = DataLahan::value('bpdas') ?? config('lahan.bpdas_default');

        return view('pages.landing', [
            'landing' => ['stats' => [
                ['label' => 'Luas Lahan Kritis', 'value' => Fmt::ha($kritisHa).' Ha', 'sub' => "Berdasarkan data {$jmlKec} Kecamatan", 'tone' => 'red'],
                ['label' => 'Terehabilitasi', 'value' => Fmt::ha($real).' Ha', 'sub' => "{$persen}% Dari Target Total", 'tone' => 'green'],
                ['label' => 'Program Aktif', 'value' => $publik->where('status', 'Berjalan')->count().' Kegiatan', 'sub' => 'Tahun Anggaran '.now()->year, 'tone' => 'amber'],
                ['label' => 'Pembina Wilayah', 'value' => 'BPDAS '.$bpdas, 'sub' => 'Kota Bengkulu', 'tone' => 'blue'],
            ]],
            'kecamatan'   => $kecamatan,
            'publik'      => $publik,
            'kritis_opts' => Catalog::kritisAda(publik: true),
            'status_opts' => config('lahan.status'),
            'app'         => Catalog::app($kecamatan, $publik),
        ]);
    }

    /** Sumber data polygon peta (publik): FeatureCollection dari tabel data_lahan. */
    public function geojson(Request $request)
    {
        $fc = [
            'type'     => 'FeatureCollection',
            // Pengunjung publik hanya melihat dampak kegiatan yang dipublikasikan; pengguna login melihat semuanya.
            'features' => DataLahan::denganRehabilitasi(! auth()->check())->orderBy('id_lahan')->get()->map->toFeature()->all(),
        ];

        $response = response()->json($fc, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/geo+json');

        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->header('Cache-Control', 'public, max-age=0, must-revalidate');
        $response->header('Vary', 'Cookie');
        $response->isNotModified($request);

        return $response;
    }

    private function labelKritis(): array
    {
        return collect(config('lahan.kritis_dihitung'))->map(fn ($c) => config("lahan.kritis.{$c}"))->all();
    }
}
