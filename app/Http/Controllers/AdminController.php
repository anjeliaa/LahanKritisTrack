<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;
use App\Models\Monitoring;
use App\Models\User;
use App\Support\Catalog;
use App\Support\Fmt;
use App\Support\Ui;

class AdminController extends InternalController
{
    protected string $role = 'admin';

    public function dashboard()
    {
        $kegiatan = KegiatanRehabilitasi::with(['lahan', 'monitoringTerakhir'])->get();
        $labelHitung = collect(config('lahan.kritis_dihitung'))->map(fn ($c) => config("lahan.kritis.{$c}"));

        // --- KPI ---
        // Status "tampil": petak yang kegiatan rehabilitasinya sudah Selesai tidak lagi dihitung sebagai lahan kritis.
        $lahan = DataLahan::select('id_lahan', 'kritis', 'luas_ha')->denganRehabilitasi()->get();
        $totalKritis = (float) $lahan
            ->filter(fn ($l) => $labelHitung->contains($l->kritis) && ! $l->sudah_direhabilitasi)
            ->sum('luas_ha');
        $jmlKec = Catalog::kecamatan()->count(); // kecamatan yang memiliki data lahan
        $target = (float) $kegiatan->sum('target_luas');
        $real = (float) $kegiatan->sum(fn ($k) => $k->realisasi);
        $persen = $target > 0 ? round($real / $target * 100, 1) : 0;
        $totalMon = Monitoring::count();
        $petugasMon = Monitoring::whereNotNull('id_user')->distinct()->count('id_user');

        $kpi = [
            ['label' => 'Total luas lahan kritis', 'value' => Fmt::ha($totalKritis), 'unit' => 'Ha', 'sub' => "Berdasarkan data {$jmlKec} Kecamatan", 'tone' => 'red', 'icon' => 'image'],
            ['label' => 'Program aktif', 'value' => (string) $kegiatan->where('status', 'Berjalan')->count(), 'unit' => 'Program', 'sub' => 'Dalam Pemantauan', 'tone' => 'green', 'icon' => 'shovel'],
            ['label' => 'Lahan terehabilitasi', 'value' => Fmt::ha($real), 'unit' => 'Ha', 'sub' => "{$persen}% Dari Target Total", 'tone' => 'blue', 'icon' => 'chartline'],
            ['label' => 'Total pemantauan', 'value' => (string) $totalMon, 'unit' => 'Sesi', 'sub' => "Dicatat {$petugasMon} Petugas", 'tone' => 'amber', 'icon' => 'clipcheck'],
        ];

        // --- donut: luas per tingkat kekritisan (urutan = warna pada dashboard.js) ---
        // Hanya kelas yang benar-benar ada pada data (mis. "Sangat Kritis" tidak muncul bila 0 petak),
        // urutan & warna sama dengan legenda peta.
        // Dikelompokkan menurut kelas tampil; "Telah Direhabilitasi" tampil tepat setelah "Tidak Kritis".
        $perKelas = $lahan->groupBy('kritis_tampil_class')->map(fn ($g) => round((float) $g->sum('luas_ha'), 2));
        $urut = collect(array_keys(config('lahan.kritis')))->flatMap(fn ($c) => $c === 'tidak' ? ['tidak', 'pulih'] : [$c]);
        $ada = $urut->filter(fn ($c) => $perKelas->has($c))->values();
        $donut = [
            'labels' => $ada->map(fn ($c) => $c === 'pulih' ? config('lahan.pulih.label') : config("lahan.kritis.{$c}"))->all(),
            'values' => $ada->map(fn ($c) => $perKelas[$c])->all(),
            'colors' => $ada->map(fn ($c) => config("lahan.warna.{$c}"))->all(),
        ];

        // --- bar: target vs realisasi per kecamatan ---
        $perKec = $kegiatan->groupBy(fn ($k) => $k->lahan?->kecamatan ?? '-')->map(fn ($g) => [
            'target' => round($g->sum('target_luas'), 2),
            'real'   => round($g->sum(fn ($k) => $k->realisasi), 2),
        ])->sortKeys();
        $bars = ['labels' => $perKec->keys()->values()->all(), 'target' => $perKec->pluck('target')->values()->all(), 'real' => $perKec->pluck('real')->values()->all()];

        // --- timeline aktivitas monitoring lapangan terbaru ---
        // Warna mengikuti pita progres: <25% kuning, 25-49% biru, 50-99% hijau, 100% gelap (selesai).
        $pita = fn (int $p) => match (true) {
            $p >= 100 => ['chip' => 'dark',  'pill' => 'dark'],
            $p >= 50  => ['chip' => 'green', 'pill' => 'green'],
            $p >= 25  => ['chip' => 'blue',  'pill' => 'blue'],
            default   => ['chip' => 'amber', 'pill' => 'red'],
        };

        $feed = Monitoring::with(['kegiatan.lahan', 'petugas'])
            ->orderByDesc('tanggal_monitoring')->orderByDesc('id_monitoring')->limit(5)->get()
            ->map(function ($m) use ($pita) {
                $warna = $pita((int) $m->progres_persen);

                return [
                    'tanggal'  => $m->tanggal_monitoring->format('Y-m-d'),
                    'judul'    => $m->kegiatan?->nama_kegiatan ?? '(kegiatan dihapus)',
                    'catatan'  => $m->catatan ?: "Progres fisik tercatat {$m->progres_persen}%, kondisi lahan {$m->kondisi_lahan}.",
                    'petugas'  => $m->petugas?->nama ?? '—',
                    'lokasi'   => $m->kegiatan?->lahan?->kecamatan ?? '-',
                    'progres'  => (int) $m->progres_persen,
                    'selesai'  => $m->kegiatan?->status === 'Selesai' && (int) $m->progres_persen >= 100,
                    'foto'     => $m->foto_url,
                    'chip'     => $warna['chip'],
                    'pill'     => $warna['pill'],
                ];
            })->all();

        $dash = compact('kpi', 'donut', 'bars', 'feed');

        return $this->render('admin.dashboard', 'dashboard', 'admin.dashboard', [
            Ui::tile('Tahun Anggaran', now()->year, 'Periode berjalan', 'white'),
            Ui::tile('Cakupan', $jmlKec, 'Kecamatan memiliki data', 'green'),
            Ui::tile('Sumber Data', 'BPDAS', 'Ketahun Bengkulu', 'blue'),
        ], [
            'dash'      => $dash,
            'kegiatan'  => collect(),
            'app_extra' => ['dashboard' => ['donut' => $donut, 'bars' => $bars]],
        ]);
    }

    public function akun()
    {
        $users = User::orderBy('id_user')->get()->map(fn ($u) => [
            'id'         => $u->id_user,
            'nama'       => $u->nama,
            'username'   => $u->username,
            'role'       => config("lahan.role.{$u->role}"),
            'role_class' => $u->role,
            'dibuat'     => Fmt::tanggal($u->created_at),
            'is_self'    => $u->id_user === auth()->id(),
            'fill'       => json_encode(['nama' => $u->nama, 'username' => $u->username, 'role' => $u->role], JSON_UNESCAPED_UNICODE),
            'url'        => route('admin.akun.update', $u->id_user),
            'url_destroy' => route('admin.akun.destroy', $u->id_user),
        ]);

        return $this->render('admin.akun', 'akun', 'admin.akun', [
            Ui::tile('Total Akun', $users->count(), 'Terdaftar', 'white'),
            Ui::tile('Admin', $users->where('role_class', 'admin')->count(), 'Akses penuh', 'amber'),
            Ui::tile('Petugas', $users->where('role_class', 'petugas')->count(), 'Lapangan', 'green'),
        ], ['users' => $users, 'kegiatan' => collect()]);
    }

    public function lahan()
    {
        $lahan = DataLahan::denganRehabilitasi()->orderBy('kecamatan')->orderBy('id_lahan')->get();
        $kegiatan = Catalog::kegiatan();
        $kritisTinggi = $lahan->whereIn('kritis', [config('lahan.kritis.kritis'), config('lahan.kritis.sangat')])->count();

        return $this->render('admin.lahan', 'lahan', 'admin.lahan', [
            Ui::tile('Petak Lahan', $lahan->count(), $lahan->pluck('kecamatan_id')->unique()->count().' kecamatan', 'white'),
            Ui::tile('Total Luas', Fmt::ha($lahan->sum('luas_ha')), 'Hektare', 'amber'),
            Ui::tile('Kritis', $kritisTinggi, 'Petak (kritis & sangat kritis)', 'red'),
            Ui::tile('Potensial', $lahan->where('kritis', config('lahan.kritis.potensial'))->count(), 'Petak', 'green'),
        ], [
            'lahan'    => $lahan->map(fn ($l) => Catalog::lahanRow($l)),
            'kegiatan' => $kegiatan,
            'kecamatan_semua' => collect(config('lahan.kecamatan'))->map(fn ($k, $id) => ['id' => $id] + $k)->values(),
        ]);
    }

    public function kegiatan()
    {
        $kegiatan = Catalog::kegiatan();
        [$total, $berjalan, $selesai, $rencana] = $this->hitungStatus($kegiatan);

        return $this->render('admin.kegiatan', 'kegiatan', 'admin.kegiatan', [
            Ui::tile('Total Kegiatan', $total, 'Program', 'white'),
            Ui::tile('Berjalan', $berjalan, 'Aktif', 'green'),
            Ui::tile('Selesai', $selesai, 'Tuntas', 'blue'),
            Ui::tile('Rencana', $rencana, 'Menunggu', 'amber'),
        ], ['kegiatan' => $kegiatan]);
    }

    public function monitoring()
    {
        $kegiatan = Catalog::kegiatan();
        $monitoring = Catalog::monitoring(auth()->user());
        $membaik = $monitoring->where('kondisi', 'Membaik')->count();

        return $this->render('admin.monitoring', 'monitoring', 'admin.monitoring', [
            Ui::tile('Total Sesi', $monitoring->count(), 'Pemantauan', 'white'),
            Ui::tile('Rata-rata Progres', round($kegiatan->avg('progres') ?? 0).'%', 'Semua kegiatan', 'green'),
            Ui::tile('Petugas Aktif', Monitoring::whereNotNull('id_user')->distinct()->count('id_user'), 'Mencatat monitoring', 'amber'),
            Ui::tile('Kondisi', ($monitoring->count() ? round($membaik / $monitoring->count() * 100) : 0).'%', 'Membaik', 'blue'),
        ], ['kegiatan' => $kegiatan, 'monitoring' => $monitoring, 'monitoring_ringkas' => Catalog::monitoringRingkas($monitoring)]);
    }
}
