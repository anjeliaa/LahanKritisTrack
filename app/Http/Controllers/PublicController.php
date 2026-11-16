<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\Catalog;
use App\Support\Fmt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function landing(Request $request)
    {
        // Kegiatan yang dipublikasikan
        $publik = Catalog::kegiatan(
            publishedOnly: true,
            forPublic: true
        );

        $kecamatan = Catalog::kecamatanSemua();
        $jmlKecData = Catalog::kecamatan()->count();

        // Statistik lahan kritis
        $kritisHa = DataLahan::select(
                'id_lahan',
                'kritis',
                'luas_ha',
                'sudah_direhabilitasi'
            )
            ->denganRehabilitasi(true)
            ->get()
            ->filter(fn ($l) =>
                in_array($l->kritis, $this->labelKritis(), true)
                && ! $l->sudah_direhabilitasi
            )
            ->sum('luas_ha');

        $jmlKec = $jmlKecData;
        $target = $publik->sum('target');
        $real = $publik->sum('realisasi');

        $persen = $target > 0
            ? round($real / $target * 100, 1)
            : 0;

        $bpdas = DataLahan::value('bpdas')
            ?? config('lahan.bpdas_default');

        // ==============================================
        // DATA VISUALISASI TEMPORAL
        // ==============================================

        // Validasi sumber data
        $sumber = $request->query('sumber', 'dummy');

        if (! in_array($sumber, ['asli', 'dummy'], true)) {
            $sumber = 'dummy';
        }

        // Ambil tahun yang tersedia sesuai sumber data
        $tahunList = DB::table('data_lahan_temporal')
            ->where('data_source', $sumber)
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->map(fn ($tahun) => (int) $tahun)
            ->values();

        // Default tahun
        $tahunAwalDefault = $tahunList->min() ?? 2020;
        $tahunAkhirDefault = $tahunList->max() ?? 2026;

        // Ambil input tahun dari URL
        $tahunAwal = (int) $request->query(
            'tahun_awal',
            $tahunAwalDefault
        );

        $tahunAkhir = (int) $request->query(
            'tahun_akhir',
            $tahunAkhirDefault
        );

        // Validasi tahun awal
        if (! $tahunList->contains($tahunAwal)) {
            $tahunAwal = $tahunAwalDefault;
        }

        // Validasi tahun akhir
        if (! $tahunList->contains($tahunAkhir)) {
            $tahunAkhir = $tahunAkhirDefault;
        }

        // Pastikan tahun awal tidak melebihi tahun akhir
        if ($tahunAwal > $tahunAkhir) {
            [$tahunAwal, $tahunAkhir] = [
                $tahunAkhir,
                $tahunAwal
            ];
        }

        // Grafik total luas lahan berdasarkan rentang tahun
        $grafikTemporal = DB::table('data_lahan_temporal')
            ->select(
                'tahun',
                DB::raw('SUM(luas_ha) as total_luas')
            )
            ->where('data_source', $sumber)
            ->whereBetween('tahun', [
                $tahunAwal,
                $tahunAkhir
            ])
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get();

        // Total luas tahun awal
        $totalAwal = DB::table('data_lahan_temporal')
            ->where('tahun', $tahunAwal)
            ->where('data_source', $sumber)
            ->sum('luas_ha');

        // Total luas tahun akhir
        $totalAkhir = DB::table('data_lahan_temporal')
            ->where('tahun', $tahunAkhir)
            ->where('data_source', $sumber)
            ->sum('luas_ha');

        // Perubahan luas
        $perubahanLuas = $totalAkhir - $totalAwal;

        // Persentase perubahan
        $persentasePerubahan = $totalAwal > 0
            ? ($perubahanLuas / $totalAwal) * 100
            : 0;

        // Rekap kategori lahan kritis pada tahun akhir
        $rekapTemporal = DB::table('data_lahan_temporal')
            ->select(
                'kritis',
                DB::raw('SUM(luas_ha) as total_luas')
            )
            ->where('tahun', $tahunAkhir)
            ->where('data_source', $sumber)
            ->groupBy('kritis')
            ->orderBy('kritis')
            ->get();

        // Jumlah data pada tahun akhir
        $jumlahDataTemporal = DB::table('data_lahan_temporal')
            ->where('tahun', $tahunAkhir)
            ->where('data_source', $sumber)
            ->count();

        // Status ketersediaan data temporal
        $temporalAdaData = $tahunList->isNotEmpty()
            && $jumlahDataTemporal > 0;

        // ==============================================
        // DATA UNTUK VIEW
        // ==============================================

        return view('pages.landing', [
            'landing' => [
                'stats' => [
                    [
                        'label' => 'Luas Lahan Kritis',
                        'value' => Fmt::ha($kritisHa).' Ha',
                        'sub' => "Berdasarkan data {$jmlKec} Kecamatan",
                        'tone' => 'red',
                    ],
                    [
                        'label' => 'Terehabilitasi',
                        'value' => Fmt::ha($real).' Ha',
                        'sub' => "{$persen}% Dari Target Total",
                        'tone' => 'green',
                    ],
                    [
                        'label' => 'Program Aktif',
                        'value' => $publik
                            ->where('status', 'Berjalan')
                            ->count().' Kegiatan',
                        'sub' => 'Tahun Anggaran '.now()->year,
                        'tone' => 'amber',
                    ],
                    [
                        'label' => 'Pembina Wilayah',
                        'value' => 'BPDAS '.$bpdas,
                        'sub' => 'Kota Bengkulu',
                        'tone' => 'blue',
                    ],
                ],
            ],

            'kecamatan' => $kecamatan,
            'publik' => $publik,
            'kritis_opts' => Catalog::kritisAda(publik: true),
            'kelas' => Catalog::kelasRingkas(),
            'status_opts' => config('lahan.status'),
            'app' => Catalog::app($kecamatan, $publik),

            // Data untuk dashboard temporal
            'temporal' => [
                'tahunList' => $tahunList,
                'tahunAwal' => $tahunAwal,
                'tahunAkhir' => $tahunAkhir,
                'sumber' => $sumber,

                'grafik' => $grafikTemporal,
                'rekap' => $rekapTemporal,

                'totalAwal' => (float) $totalAwal,
                'totalAkhir' => (float) $totalAkhir,

                'perubahanLuas' => (float) $perubahanLuas,
                'persentasePerubahan' => (float) $persentasePerubahan,

                'jumlahData' => $jumlahDataTemporal,
                'adaData' => $temporalAdaData,
            ],
        ]);
    }

    /**
     * GeoJSON polygon peta publik.
     */
    public function geojson(Request $request)
    {
        $fc = [
            'type' => 'FeatureCollection',

            'features' => DataLahan::denganRehabilitasi(! auth()->check())
                ->orderBy('id_lahan')
                ->get()
                ->map
                ->toFeature()
                ->all(),
        ];

        $response = response()->json(
            $fc,
            200,
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )->header('Content-Type', 'application/geo+json');

        $response->setEtag(md5($response->getContent()));
        $response->setPublic();

        $response->header(
            'Cache-Control',
            'public, max-age=0, must-revalidate'
        );

        $response->header('Vary', 'Cookie');
        $response->isNotModified($request);

        return $response;
    }

    /**
     * Label kategori lahan kritis.
     */
    private function labelKritis(): array
    {
        return collect(config('lahan.kritis_dihitung'))
            ->map(fn ($c) => config("lahan.kritis.{$c}"))
            ->all();
    }
}
