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
            'luas_ha'  => 'float',
            'geometri' => 'array',
        ];
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
                'kritis'       => $this->kritis,
                'kritis_class' => $this->kritis_class,
                'kecamatan'    => $this->kecamatan,
                'kec_slug'     => $this->kec_slug,
                'bpdas'        => $this->bpdas,
                'luas'         => $this->luas_ha,
            ],
            'geometry'   => $this->geometri,
        ];
    }
}
