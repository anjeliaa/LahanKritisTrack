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
}
