<?php

namespace App\Models;

use Carbon\CarbonInterface;
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

    // ------------------------------------------------------------------ kepemilikan

    /** Admin boleh semua; Petugas hanya kegiatan yang ia kelola (relasi "mengelola", kolom id_user). */
    public function bolehDikelola(?User $user): bool
    {
        return $user && ($user->isAdmin() || (int) $this->id_user === (int) $user->id_user);
    }

    // ------------------------------------------------------------------ aturan status

    /**
     * Status yang boleh dipilih menurut progres monitoring TERBARU:
     *  - belum ada progres (0%)  : Rencana / Berjalan (petugas menandai kegiatan dimulai)
     *  - 1-99%                   : Berjalan
     *  - 100%                    : Selesai
     */
    public function statusDiizinkan(): array
    {
        $p = $this->progres;

        return match (true) {
            $p >= 100 => ['Selesai'],
            $p > 0    => ['Berjalan'],
            default   => ['Rencana', 'Berjalan'],
        };
    }

    /**
     * Menerapkan status baru + mencatat tanggal perubahan status (tanggal_status).
     * Saat menjadi Selesai, tanggal_selesai yang masih kosong diisi otomatis dengan tanggal penyelesaian;
     * tanggal_selesai yang sudah diisi (target) dipertahankan, sehingga tepat waktu / terlambat dapat dibandingkan
     * dengan tanggal_status (tanggal penyelesaian sebenarnya).
     */
    public function terapkanStatus(string $status, ?CarbonInterface $tanggal = null): void
    {
        $tanggal ??= today();

        if ($this->status !== $status) {
            $this->tanggal_status = $tanggal;
        }

        $this->status = $status;

        if ($status === 'Selesai' && ! $this->tanggal_selesai) {
            $this->tanggal_selesai = $tanggal;
        }

        $this->save();
    }

    /**
     * Menyelaraskan status dengan progres monitoring terbaru. Dipanggil setiap monitoring
     * ditambah / diubah / dihapus:
     *   100% -> Selesai;  1-99% -> Berjalan;  0% / tanpa monitoring -> tidak diubah (kecuali sebelumnya Selesai).
     */
    public function selaraskanStatus(): void
    {
        $this->unsetRelation('monitoringTerakhir');
        $terakhir = $this->monitoringTerakhir;
        $p = (int) ($terakhir?->progres_persen ?? 0);
        $tgl = $terakhir?->tanggal_monitoring;

        $target = match (true) {
            $p >= 100 => 'Selesai',
            $p > 0    => 'Berjalan',
            default   => $this->status === 'Selesai' ? 'Berjalan' : null,
        };

        if ($target !== null && $target !== $this->status) {
            $this->terapkanStatus($target, $tgl);
        }
    }
}
