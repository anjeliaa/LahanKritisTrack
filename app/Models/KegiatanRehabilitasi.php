<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KegiatanRehabilitasi extends Model
{
    protected $table = 'kegiatan_rehabilitasi';

    protected $primaryKey = 'id_kegiatan';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'target_luas'     => 'float',
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_status'  => 'date',
            'is_published'    => 'boolean',
        ];
    }

    /** relasi "direhabilitasi" */
    public function lahan(): BelongsTo
    {
        return $this->belongsTo(DataLahan::class, 'id_lahan', 'id_lahan');
    }

    /** relasi "mengelola" */
    public function pengelola(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** relasi "dipantau" */
    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'id_kegiatan', 'id_kegiatan');
    }

    /** monitoring terbaru: dasar perhitungan progres & realisasi */
    public function monitoringTerakhir(): HasOne
    {
        return $this->hasOne(Monitoring::class, 'id_kegiatan', 'id_kegiatan')
            ->ofMany(['tanggal_monitoring' => 'max', 'id_monitoring' => 'max']);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /** ID program tampilan (tahun registrasi + nomor), mis. PRJ-2026-03 */
    public function getKodeAttribute(): string
    {
        $tahun = ($this->created_at ?? now())->format('Y');

        return sprintf('PRJ-%s-%02d', $tahun, $this->id_kegiatan);
    }

    /** Progres (%) = progres pada monitoring terbaru (0 bila belum pernah dimonitor). */
    public function getProgresAttribute(): int
    {
        $p = $this->monitoringTerakhir?->progres_persen ?? 0;

        return (int) $p;
    }

    /** Realisasi (Ha) = target × progres. */
    public function getRealisasiAttribute(): float
    {
        return round($this->target_luas * $this->progres / 100, 2);
    }

    public function getStatusClassAttribute(): string
    {
        return strtolower($this->status);
    }
}
