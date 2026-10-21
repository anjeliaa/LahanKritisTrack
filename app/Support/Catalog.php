<?php

namespace App\Support;

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;
use App\Models\Monitoring;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Pembentuk data tampilan dari database. Kunci array dipertahankan agar sama dengan
 * yang dipakai Blade & JavaScript (public/js/map.js, app.js).
 */
class Catalog
{
    private static function json(array $a): string
    {
        return json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Kecamatan yang memiliki data lahan (bersumber dari database). */
    public static function kecamatan(): Collection
    {
        return DataLahan::query()
            ->select('kecamatan_id', 'kecamatan')
            ->groupBy('kecamatan_id', 'kecamatan')
            ->orderBy('kecamatan')
            ->get()
            ->map(fn ($r) => [
                'id'   => (int) $r->kecamatan_id,
                'slug' => config("lahan.kecamatan.{$r->kecamatan_id}.slug", (string) $r->kecamatan_id),
                'nama' => $r->kecamatan,
                'ada_data' => true,
            ])
            ->values();
    }

    /**
     * Seluruh kecamatan Kota Bengkulu untuk pilihan FILTER, termasuk yang belum punya data lahan
     * (ditandai ada_data=false). Form input kegiatan tetap memakai kecamatan() karena butuh petak.
     */
    public static function kecamatanSemua(): Collection
    {
        $ada = DataLahan::query()->distinct()->pluck('kecamatan_id')->map(fn ($v) => (int) $v)->all();

        return collect(config('lahan.kecamatan'))
            ->map(fn ($k, $id) => ['id' => (int) $id, 'slug' => $k['slug'], 'nama' => $k['nama'], 'ada_data' => in_array((int) $id, $ada, true)])
            ->sortBy('nama')
            ->values();
    }

    /**
     * Kelas kekritisan yang benar-benar ada pada data (untuk pilihan filter & legenda).
     *
     * @param  bool  $publik   true = hanya kegiatan yang dipublikasikan yang dihitung (tampilan masyarakat)
     * @param  bool  $efektif  true = ikut menampilkan "Telah Direhabilitasi" untuk petak yang kegiatannya Selesai;
     *                         false = kelas asli data BPDAS saja (untuk pengelolaan data lahan)
     */
    public static function kritisAda(bool $publik = false, bool $efektif = true): array
    {
        $q = DataLahan::query()->select('id_lahan', 'kritis');
        if ($efektif) {
            $q->denganRehabilitasi($publik);
        }
        $rows = $q->get();

        $label = config('lahan.kritis');
        $ada = $rows->filter(fn ($r) => ! ($efektif && $r->sudah_direhabilitasi))->pluck('kritis')->unique()->all();

        $out = collect($label)
            ->filter(fn ($l) => in_array($l, $ada, true))
            ->map(fn ($l, $v) => ['v' => $v, 'l' => $l]);

        if ($efektif && $rows->contains(fn ($r) => $r->sudah_direhabilitasi)) {
            // tampil tepat setelah "Tidak Kritis"
            $pulih = ['v' => 'pulih', 'l' => config('lahan.pulih.label')];
            $out = $out->has('tidak')
                ? $out->flatMap(fn ($x, $k) => $k === 'tidak' ? [['v' => 'tidak', 'l' => $x['l']], $pulih] : [$x])->keyBy('v')
                : $out->prepend($pulih, 'pulih');
        }

        return $out->values()->all();
    }

    /** Seluruh kelas kekritisan (untuk form ubah petak oleh Admin). */
    public static function kritisOpts(): array
    {
        return collect(config('lahan.kritis'))
            ->map(fn ($label, $v) => ['v' => $v, 'l' => $label])
            ->values()
            ->all();
    }

    /** Pilihan petak untuk form kegiatan (difilter berdasarkan kecamatan oleh JS). */
    public static function petak(): Collection
    {
        return DataLahan::query()
            ->select('id_lahan', 'kecamatan_id', 'kecamatan', 'kritis', 'luas_ha')
            ->orderBy('kecamatan')->orderBy('id_lahan')
            ->get()
            ->map(fn ($l) => [
                'id'           => $l->id_lahan,
                'kecamatan_id' => (int) $l->kecamatan_id,
                'label'        => "Petak #{$l->id_lahan} · ".Fmt::ha($l->luas_ha, 2)." Ha · {$l->kritis}",
            ]);
    }

    /** @param  int|null  $userId  bila diisi: hanya kegiatan yang dikelola (id_user) pengguna tsb. */
    public static function kegiatan(bool $publishedOnly = false, bool $forPublic = false, ?int $userId = null): Collection
    {
        $q = KegiatanRehabilitasi::with(['lahan', 'monitoringTerakhir', 'pengelola'])->orderByDesc('id_kegiatan');
        if ($publishedOnly) {
            $q->published();
        }
        if ($userId !== null) {
            $q->where('id_user', $userId);
        }

        return $q->get()->map(fn ($k) => self::kegiatanRow($k, $forPublic));
    }

    public static function kegiatanRow(KegiatanRehabilitasi $k, bool $forPublic = false): array
    {
        $lahan = $k->lahan;
        $kec = $lahan?->kecamatan ?? '-';

        $row = [
            'id'            => $k->id_kegiatan,
            'kode'          => $k->kode,
            'nama'          => $k->nama_kegiatan,
            'kecamatan'     => $kec,
            'kecamatan_id'  => $lahan?->kecamatan_id,
            'kec_slug'      => $lahan?->kec_slug,
            'id_lahan'      => $k->id_lahan,
            'jenis'         => $k->jenis_kegiatan ?: '-',
            'pj'            => $k->penanggung_jawab ?: '-',
            'target'        => $k->target_luas,
            'target_fmt'    => Fmt::ha($k->target_luas),
            'realisasi'     => $k->realisasi,
            'realisasi_fmt' => Fmt::ha($k->realisasi),
            'mulai'         => Fmt::tanggal($k->tanggal_mulai),
            'selesai'       => Fmt::tanggal($k->tanggal_selesai),
            // tanggal penyelesaian sebenarnya = tanggal perubahan status menjadi Selesai
            'selesai_aktual' => $k->status === 'Selesai' && $k->tanggal_status ? Fmt::tanggal($k->tanggal_status) : null,
            'status'        => $k->status,
            'status_class'  => $k->status_class,
            'progres'       => $k->progres,
            'published'     => $k->is_published,
            'q'             => mb_strtolower("{$k->nama_kegiatan} {$k->jenis_kegiatan} {$k->penanggung_jawab} {$kec}"),
        ];

        if ($forPublic) {
            return $row;
        }

        $row['id_user'] = $k->id_user;
        $row['pengelola'] = $k->pengelola?->nama;

        // pengisi otomatis form modal (nama kunci = atribut name pada form)
        $row['fill'] = self::json([
            'nama_kegiatan'    => $k->nama_kegiatan,
            'kecamatan_id'     => $lahan?->kecamatan_id,   // harus sebelum id_lahan (mengisi daftar petak)
            'id_lahan'         => $k->id_lahan,
            'jenis_kegiatan'   => $k->jenis_kegiatan,
            'penanggung_jawab' => $k->penanggung_jawab,
            'target_luas'      => $k->target_luas,
            'tanggal_mulai'    => $k->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai'  => $k->tanggal_selesai?->format('Y-m-d'),
            'status'           => $k->status,
            'is_published'     => $k->is_published ? '1' : '0',
            'id_user'          => $k->id_user,
        ]);
        $row['fill_status'] = self::json(['kegiatan' => $k->id_kegiatan, 'status' => $k->status]);
        $row['url'] = route('kegiatan.update', $k->id_kegiatan);
        $row['url_destroy'] = route('kegiatan.destroy', $k->id_kegiatan);

        return $row;
    }

    /** Petugas hanya melihat monitoring pada kegiatan yang ia kelola (atau yang ia catat sendiri); Admin melihat semua. */
    public static function monitoring(?User $viewer = null): Collection
    {
        $q = Monitoring::with(['kegiatan.lahan', 'petugas'])
            ->orderByDesc('tanggal_monitoring')->orderByDesc('id_monitoring');

        if ($viewer && ! $viewer->isAdmin()) {
            $q->where(fn ($w) => $w->where('id_user', $viewer->id_user)
                ->orWhereHas('kegiatan', fn ($k) => $k->where('id_user', $viewer->id_user)));
        }

        return $q->get()
            ->map(fn ($m) => self::monitoringRow($m, $viewer));
    }

    public static function monitoringRow(Monitoring $m, ?User $viewer = null): array
    {
        $canEdit = $viewer && ($viewer->isAdmin() || $m->id_user === $viewer->id_user);
        $nama = $m->kegiatan?->nama_kegiatan ?? '(kegiatan dihapus)';

        return [
            'id'          => $m->id_monitoring,
            'id_kegiatan' => $m->id_kegiatan,
            'kegiatan'    => $nama,
            'lokasi'      => $m->kegiatan?->lahan?->kecamatan ?? '-',
            'id_lahan'    => $m->kegiatan?->id_lahan,
            'status'      => $m->kegiatan?->status,
            'tanggal'     => Fmt::tanggal($m->tanggal_monitoring),
            'progres'     => $m->progres_persen,
            'kondisi'     => $m->kondisi_lahan,
            'kondisi_class' => strtolower($m->kondisi_lahan),
            'catatan'     => $m->catatan ?: '-',
            'petugas'     => $m->petugas?->nama ?? '—',
            'foto'        => $m->foto_url,
            'can_edit'    => $canEdit,
            'can_delete'  => (bool) $viewer?->isAdmin(),
            'fill'        => self::json([
                'id_kegiatan'        => $m->id_kegiatan,
                'tanggal_monitoring' => $m->tanggal_monitoring->format('Y-m-d'),
                'progres_persen'     => $m->progres_persen,
                'kondisi_lahan'      => $m->kondisi_lahan,
                'catatan'            => $m->catatan,
            ]),
            // "Update status progres": menambah catatan progres baru untuk kegiatan yang sama
            'fill_progres' => self::json([
                'id_kegiatan'    => $m->id_kegiatan,
                'progres_persen' => $m->progres_persen,
                'kondisi_lahan'  => $m->kondisi_lahan,
            ]),
            'url'         => route('monitoring.update', $m->id_monitoring),
            'url_destroy' => route('monitoring.destroy', $m->id_monitoring),
        ];
    }

    /**
     * Satu baris per KEGIATAN (= per petak yang dipantau): menampilkan monitoring TERBARU,
     * sedangkan seluruh catatan sebelumnya tersedia sebagai riwayat (JSON) untuk tombol Detail.
     *
     * @param  Collection  $rows  hasil monitoring(), sudah terurut terbaru -> terlama
     */
    public static function monitoringRingkas(Collection $rows): Collection
    {
        return $rows->groupBy('id_kegiatan')->map(function ($g) {
            $terbaru = $g->first();

            $riwayat = $g->map(fn ($r) => [
                'id'            => $r['id'],
                'tanggal'       => $r['tanggal'],
                'progres'       => $r['progres'],
                'kondisi'       => $r['kondisi'],
                'kondisi_class' => $r['kondisi_class'],
                'catatan'       => $r['catatan'],
                'petugas'       => $r['petugas'],
                'foto'          => $r['foto'],
                'can_edit'      => $r['can_edit'],
                'can_delete'    => $r['can_delete'],
                'fill'          => $r['fill'],
                'url'           => $r['url'],
                'url_destroy'   => $r['url_destroy'],
            ])->values()->all();

            return $terbaru + [
                'jumlah'  => $g->count(),
                'riwayat' => self::json($riwayat),
            ];
        })->values();
    }

    public static function lahanRow(DataLahan $l): array
    {
        return [
            'id'           => $l->id_lahan,
            'objectid'     => $l->objectid,
            'wilayah'      => $l->wilayah,
            'kecamatan'    => $l->kecamatan,
            'kecamatan_id' => (int) $l->kecamatan_id,
            'bpdas'        => $l->bpdas,
            'kritis'       => $l->kritis,
            'kritis_class' => $l->kritis_class,
            'rehab'        => $l->sudah_direhabilitasi,
            'rehab_tanggal' => $l->sudah_direhabilitasi && ($l->rehab_tanggal ?? null) ? Fmt::tanggal(\Carbon\Carbon::parse($l->rehab_tanggal)) : null,
            'luas'         => $l->luas_ha,
            'luas_fmt'     => Fmt::ha($l->luas_ha, 2),
            'q'            => mb_strtolower("{$l->kecamatan} {$l->kritis} {$l->bpdas} {$l->id_lahan}"),
            'detail'       => self::json([
                'id_lahan' => $l->id_lahan,
                'nama'     => $l->kecamatan,
                'kec'      => $l->kecamatan,
                'kritis'   => $l->kritis,
                'luas'     => Fmt::ha($l->luas_ha, 2).' Ha',
                'bpdas'    => 'BPDAS '.$l->bpdas,
            ]),
            'fill'         => self::json([
                'kecamatan_id' => (int) $l->kecamatan_id,
                'wilayah'      => $l->wilayah,
                'bpdas'        => $l->bpdas,
                'kritis'       => $l->kritis,
                'luas_ha'      => $l->luas_ha,
            ]),
            'url'          => route('admin.lahan.update', $l->id_lahan),
            'url_destroy'  => route('admin.lahan.destroy', $l->id_lahan),
        ];
    }

    /** Data untuk window.APP pada halaman peta/dashboard. */
    public static function app(Collection $kecamatan, Collection $kegiatan, array $extra = []): array
    {
        return array_merge([
            'kecamatan' => $kecamatan->all(),
            'kegiatan'  => $kegiatan->all(),
            'warna'     => config('lahan.warna'),
        ], $extra);
    }
}
