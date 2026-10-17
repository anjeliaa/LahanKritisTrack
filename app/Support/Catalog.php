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
            ])
            ->values();
    }

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

    public static function kegiatan(bool $publishedOnly = false, bool $forPublic = false): Collection
    {
        $q = KegiatanRehabilitasi::with(['lahan', 'monitoringTerakhir'])->orderByDesc('id_kegiatan');
        if ($publishedOnly) {
            $q->published();
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
            'status'        => $k->status,
            'status_class'  => $k->status_class,
            'progres'       => $k->progres,
            'published'     => $k->is_published,
            'q'             => mb_strtolower("{$k->nama_kegiatan} {$k->jenis_kegiatan} {$k->penanggung_jawab} {$kec}"),
        ];

        if ($forPublic) {
            return $row;
        }

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
        ]);
        $row['fill_status'] = self::json(['kegiatan' => $k->id_kegiatan, 'status' => $k->status]);
        $row['url'] = route('kegiatan.update', $k->id_kegiatan);
        $row['url_destroy'] = route('kegiatan.destroy', $k->id_kegiatan);

        return $row;
    }

    public static function monitoring(?User $viewer = null): Collection
    {
        return Monitoring::with(['kegiatan.lahan', 'petugas'])
            ->orderByDesc('tanggal_monitoring')->orderByDesc('id_monitoring')
            ->get()
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
            'tanggal'     => Fmt::tanggal($m->tanggal_monitoring),
            'progres'     => $m->progres_persen,
            'kondisi'     => $m->kondisi_lahan,
            'kondisi_class' => strtolower($m->kondisi_lahan),
            'catatan'     => $m->catatan ?: '-',
            'petugas'     => $m->petugas?->nama ?? '—',
            'foto'        => $m->foto_url,
            'can_edit'    => $canEdit,
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
