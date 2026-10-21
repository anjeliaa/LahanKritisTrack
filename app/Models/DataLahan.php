<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataLahan extends Model
{
    protected $table = 'data_lahan';

    protected $primaryKey = 'id_lahan';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'luas_ha'       => 'float',
            'geometri'      => 'array',
            'rehab_selesai' => 'boolean',   // hasil scopeDenganRehabilitasi()
        ];
    }

    /**
     * Menambahkan penanda "sudah direhabilitasi": petak memiliki kegiatan berstatus Selesai.
     * $hanyaPublished = true untuk tampilan publik (hanya kegiatan yang dipublikasikan yang dihitung).
     */
    public function scopeDenganRehabilitasi($query, bool $hanyaPublished = false)
    {
        $selesai = fn ($k) => $k->where('status', 'Selesai')->when($hanyaPublished, fn ($x) => $x->where('is_published', true));

        return $query
            ->withExists(['kegiatan as rehab_selesai' => $selesai])
            ->withMax(['kegiatan as rehab_tanggal' => $selesai], 'tanggal_status');
    }

    public function getSudahDirehabilitasiAttribute(): bool
    {
        return (bool) ($this->attributes['rehab_selesai'] ?? false);
    }

    /** Kelas yang DITAMPILKAN pada peta: petak yang sudah selesai direhabilitasi tidak lagi tampil sebagai lahan kritis. */
    public function getKritisTampilClassAttribute(): string
    {
        return $this->sudah_direhabilitasi ? 'pulih' : $this->kritis_class;
    }

    public function getKritisTampilAttribute(): string
    {
        return $this->sudah_direhabilitasi ? config('lahan.pulih.label') : $this->kritis;
    }

    /** kelas kekritisan (tidak|potensial|agak|kritis|sangat) dari label `kritis` */
    public function getKritisClassAttribute(): string
    {
        $found = array_search($this->kritis, config('lahan.kritis'), true);

        return $found === false ? 'tidak' : $found;
    }

    public function getKecSlugAttribute(): string
    {
        return config("lahan.kecamatan.{$this->kecamatan_id}.slug", '');
    }

    /** relasi "direhabilitasi" */
    public function kegiatan(): HasMany
    {
        return $this->hasMany(KegiatanRehabilitasi::class, 'id_lahan', 'id_lahan');
    }

    /** Feature GeoJSON untuk peta (properti sama dengan yang dibaca public/js/map.js). */
    public function toFeature(): array
    {
        return [
            'type'       => 'Feature',
            'properties' => [
                'id_lahan'     => $this->id_lahan,
                'kritis'       => $this->kritis_tampil,          // label yang ditampilkan
                'kritis_class' => $this->kritis_tampil_class,   // kelas warna/filter pada peta
                'kritis_asli'  => $this->kritis,                // data BPDAS (tidak diubah)
                'rehab'        => $this->sudah_direhabilitasi,
                'rehab_tanggal' => $this->sudah_direhabilitasi && ($this->attributes['rehab_tanggal'] ?? null)
                    ? \App\Support\Fmt::tanggal(\Carbon\Carbon::parse($this->attributes['rehab_tanggal'])) : null,
                'kecamatan'    => $this->kecamatan,
                'kec_slug'     => $this->kec_slug,
                'bpdas'        => $this->bpdas,
                'luas'         => $this->luas_ha,
            ],
            'geometry'   => $this->geometri,
        ];
    }
}
