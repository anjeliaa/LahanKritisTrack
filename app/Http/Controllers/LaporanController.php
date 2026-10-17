<?php

namespace App\Http\Controllers;

use App\Models\KegiatanRehabilitasi;
use App\Support\Fmt;
use App\Support\SimpleXlsx;
use App\Support\Ui;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LaporanController extends InternalController
{
    protected string $role = 'admin';

    public function index(Request $request)
    {
        [$rows, $periode] = $this->data($request);
        $n = $rows->count();
        $target = $rows->sum('target');
        $real = $rows->sum('realisasi');

        $lap = [
            'nomor'   => 'LKT/'.now()->format('m').'/'.now()->year,
            'periode' => $periode['label'],
            'tanggal' => 'Bengkulu, '.Fmt::tanggal(now(), 'd F Y'),
            'rows'    => $rows,
            'total_target'    => Fmt::ha($target),
            'total_realisasi' => Fmt::ha($real),
            'total_progres'   => Fmt::ha($target > 0 ? $real / $target * 100 : 0),
        ];

        $berjalan = $rows->where('status', 'Berjalan')->count();
        $selesai = $rows->where('status', 'Selesai')->count();
        $pct = fn ($x) => $n ? round($x / $n * 100).'%' : '0%';

        $filter = [
            'kecamatan_id' => $request->query('kecamatan_id', ''),
            'status'       => $request->query('status', ''),
            'periode'      => $request->query('periode', ''),
        ];

        return $this->render('admin.laporan', 'laporan', 'admin.laporan', [
            Ui::tile('Dilaporkan', $n, 'Kegiatan', 'white'),
            Ui::tile('Berjalan', $pct($berjalan), "{$berjalan} dari {$n}", 'green'),
            Ui::tile('Selesai', $pct($selesai), "{$selesai} dari {$n}", 'blue'),
            Ui::tile('Periode', $periode['tile'], 'Tahun anggaran', 'amber'),
        ], [
            'lap'          => $lap,
            'filter'       => $filter,
            'periode_opts' => $this->periodeOpts(),
            'kegiatan'     => collect(),
            'excel_url'    => route('admin.laporan.excel', array_filter($filter, fn ($v) => $v !== '' && $v !== null)),
        ]);
    }

    public function excel(Request $request)
    {
        [$rows, $periode] = $this->data($request);

        $out = [['No', 'ID Program', 'Nama Kegiatan', 'Kecamatan', 'Jenis Kegiatan', 'Penanggung Jawab', 'Target (Ha)', 'Realisasi (Ha)', 'Progres (%)', 'Status', 'Mulai', 'Selesai']];
        foreach ($rows->values() as $i => $r) {
            $out[] = [$i + 1, $r['kode'], $r['nama'], $r['kecamatan'], $r['jenis'], $r['pj'], (float) $r['target'], (float) $r['realisasi'], (int) $r['progres'], $r['status'], $r['mulai'], $r['selesai']];
        }
        $out[] = ['', '', 'TOTAL', '', '', '', (float) $rows->sum('target'), (float) $rows->sum('realisasi'), '', '', '', ''];

        $nama = 'laporan-lahankritistrack-'.now()->format('Ymd-His');

        if (SimpleXlsx::available()) {
            $path = SimpleXlsx::write($out, 'Rekap Kegiatan', [5, 14, 48, 20, 22, 28, 12, 14, 12, 12, 14, 14]);

            return response()->download($path, $nama.'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        // cadangan bila ekstensi zip tidak aktif: CSV titik-koma (terbaca Excel versi Indonesia)
        $csv = "\xEF\xBB\xBF".collect($out)->map(fn ($r) => collect($r)->map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"')->implode(';'))->implode("\r\n");

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nama.'.csv"',
        ]);
    }

    /** @return array{0:Collection,1:array{label:string,tile:string}} */
    private function data(Request $request): array
    {
        $q = KegiatanRehabilitasi::with(['lahan', 'monitoringTerakhir'])->orderBy('id_kegiatan');

        if ($kec = $request->query('kecamatan_id')) {
            $q->whereHas('lahan', fn ($l) => $l->where('kecamatan_id', (int) $kec));
        }
        if (($st = $request->query('status')) && in_array($st, config('lahan.status'), true)) {
            $q->where('status', $st);
        }

        $label = 'Seluruh periode (per '.Fmt::tanggal(now(), 'd F Y').')';
        $tile = 'Semua';

        if ($p = $this->parsePeriode((string) $request->query('periode'))) {
            [$from, $to, $label, $tile] = $p;
            // kegiatan yang jangka waktunya beririsan dengan periode
            $q->where(fn ($w) => $w->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $to->toDateString()))
              ->where(fn ($w) => $w->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $from->toDateString()));
        }

        return [$q->get()->map(fn ($k) => \App\Support\Catalog::kegiatanRow($k, true)), ['label' => $label, 'tile' => $tile]];
    }

    /** "y:2026" | "s1:2026" | "s2:2026" */
    private function parsePeriode(string $v): ?array
    {
        if (! preg_match('/^(y|s1|s2):(\d{4})$/', $v, $m)) {
            return null;
        }
        $y = (int) $m[2];

        return match ($m[1]) {
            'y'  => [Carbon::create($y, 1, 1), Carbon::create($y, 12, 31), "Periode 01 Januari – 31 Desember {$y}", (string) $y],
            's1' => [Carbon::create($y, 1, 1), Carbon::create($y, 6, 30), "Periode 01 Januari – 30 Juni {$y}", "Sem. I {$y}"],
            's2' => [Carbon::create($y, 7, 1), Carbon::create($y, 12, 31), "Periode 01 Juli – 31 Desember {$y}", "Sem. II {$y}"],
        };
    }

    private function periodeOpts(): array
    {
        $min = KegiatanRehabilitasi::min('tanggal_mulai');
        $max = KegiatanRehabilitasi::max('tanggal_selesai');
        $from = $min ? Carbon::parse($min)->year : now()->year;
        $to = max($max ? Carbon::parse($max)->year : now()->year, now()->year);

        $opts = [];
        for ($y = $to; $y >= $from; $y--) {
            $opts["y:{$y}"] = "Tahun {$y}";
            $opts["s1:{$y}"] = "Semester I {$y}";
            $opts["s2:{$y}"] = "Semester II {$y}";
        }

        return $opts;
    }
}
