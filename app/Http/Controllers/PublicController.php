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
        $kritisHa = DataLahan::select('id_lahan', 'kritis', 'luas_ha')
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
        // DATA UNTUK VIEW
        // ==============================================

        // Tahun yang dapat dipilih pada peta: tahun-tahun riwayat + tahun data utama
        $tahunUtama = (int) config('lahan.tahun_data', 2026);
        $tahunList = DB::table('data_lahan_temporal')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->push($tahunUtama)
            ->unique()
            ->sort()
            ->values();

        return view('pages.landing', [
            'tahun_list' => $tahunList,
            'tahun_utama' => $tahunUtama,
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
        ]);
    }

    /**
     * GeoJSON polygon peta publik.
     */
    public function geojson(Request $request)
    {
        $tahunUtama = (int) config('lahan.tahun_data', 2026);
        $tahun = (int) $request->query('tahun', $tahunUtama);

        $lahan = DataLahan::denganRehabilitasi(! auth()->check())
            ->orderBy('id_lahan')
            ->get();

        if ($tahun === $tahunUtama) {
            $features = $lahan->map->toFeature()->all();
        } else {
            // tahun sebelumnya: kelas dan luas dari riwayat; penanda "telah direhabilitasi" hanya berlaku untuk kondisi terkini
            $riwayat = DB::table('data_lahan_temporal')->where('tahun', $tahun)->get()->keyBy('id_lahan');
            abort_if($riwayat->isEmpty(), 404);

            $features = $lahan->map(function ($l) use ($riwayat) {
                $f = $l->toFeature();
                if ($r = $riwayat->get($l->id_lahan)) {
                    $kelas = array_search($r->kritis, config('lahan.kritis'), true);
                    $f['properties'] = array_merge($f['properties'], [
                        'kritis'        => $r->kritis,
                        'kritis_class'  => $kelas === false ? 'tidak' : $kelas,
                        'kritis_asli'   => $r->kritis,
                        'rehab'         => false,
                        'rehab_tanggal' => null,
                        'luas'          => (float) $r->luas_ha,
                    ]);
                }

                return $f;
            })->all();
        }

        $fc = ['type' => 'FeatureCollection', 'features' => $features];

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
     * Ringkasan per tahun (jumlah petak dan luas per kelas kekritisan) untuk perbandingan tahun pada peta.
     * Dihitung dari data kelas asli, tanpa penanda "telah direhabilitasi".
     */
    public function temporal()
    {
        $tahunUtama = (int) config('lahan.tahun_data', 2026);
        $label = config('lahan.kritis');
        $kelas = fn ($k) => ($c = array_search($k, $label, true)) === false ? 'tidak' : $c;
        $data = [];

        $isi = function (int $tahun, $baris) use (&$data, $kelas) {
            foreach ($baris as $b) {
                $c = $kelas($b->kritis);
                $data[$tahun][$c]['n'] = ($data[$tahun][$c]['n'] ?? 0) + (int) $b->n;
                $data[$tahun][$c]['ha'] = round(($data[$tahun][$c]['ha'] ?? 0) + (float) $b->ha, 2);
            }
        };

        $isi($tahunUtama, DataLahan::select('kritis', DB::raw('count(*) as n'), DB::raw('sum(luas_ha) as ha'))->groupBy('kritis')->get());

        $riwayat = DB::table('data_lahan_temporal')
            ->select('tahun', 'kritis', DB::raw('count(*) as n'), DB::raw('sum(luas_ha) as ha'))
            ->groupBy('tahun', 'kritis')
            ->get()
            ->groupBy('tahun');
        foreach ($riwayat as $tahun => $baris) {
            $isi((int) $tahun, $baris);
        }
        ksort($data);

        return response()->json([
            'tahun_utama' => $tahunUtama,
            'tahun'       => array_map('intval', array_keys($data)),
            'dihitung'    => config('lahan.kritis_dihitung'),
            'data'        => $data,
        ]);
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
