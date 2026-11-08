<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Monitoring extends Model
{
    protected $table = 'monitoring';

    protected $primaryKey = 'id_monitoring';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_monitoring' => 'date',
            'progres_persen'     => 'integer',
        ];
    }

    /** relasi "dipantau" */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanRehabilitasi::class, 'id_kegiatan', 'id_kegiatan');
    }

    /** relasi "mencatat" */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_dokumentasi ? Storage::disk('public')->url($this->foto_dokumentasi) : null;
    }

    /** Nilai yang tampil ke pengguna (label => teks), dipakai membandingkan sebelum/sesudah Admin mengubah. */
    public function jejak(): array
    {
        return [
            'Kegiatan'           => KegiatanRehabilitasi::whereKey($this->id_kegiatan)->value('nama_kegiatan') ?? '-',
            'Tanggal monitoring' => $this->tanggal_monitoring?->locale('id')->translatedFormat('j F Y') ?? '-',
            'Progres'            => $this->progres_persen.'%',
            'Kondisi lahan'      => (string) $this->kondisi_lahan,
            'Catatan'            => trim((string) $this->catatan) !== '' ? trim((string) $this->catatan) : '-',
            'Foto dokumentasi'   => $this->foto_dokumentasi ? basename($this->foto_dokumentasi) : '-',
        ];
    }
}
