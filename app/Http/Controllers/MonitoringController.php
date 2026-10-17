<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonitoringController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['id_user'] = $request->user()->id_user;
        $data['tanggal_monitoring'] ??= today()->toDateString();
        $data['foto_dokumentasi'] = $this->simpanFoto($request);

        Monitoring::create($data);

        return $this->ok($request, 'Laporan monitoring disimpan.');
    }

    public function update(Request $request, Monitoring $monitoring)
    {
        // Admin boleh mengubah semua; petugas hanya laporan yang ia catat sendiri.
        abort_unless(
            $request->user()->isAdmin() || $monitoring->id_user === $request->user()->id_user,
            403,
            'Anda hanya dapat mengubah laporan monitoring yang Anda catat sendiri.'
        );

        $data = $this->validated($request);

        // tanggal kosong = pertahankan tanggal lama (kolom wajib terisi)
        if (blank($data['tanggal_monitoring'] ?? null)) {
            unset($data['tanggal_monitoring']);
        }

        // berkas foto tidak boleh ikut masuk ke update(); simpan path-nya saja bila ada unggahan baru
        unset($data['foto_dokumentasi']);
        if ($foto = $this->simpanFoto($request)) {
            if ($monitoring->foto_dokumentasi) {
                Storage::disk('public')->delete($monitoring->foto_dokumentasi);
            }
            $data['foto_dokumentasi'] = $foto;
        }

        $monitoring->update($data);

        return $this->ok($request, 'Laporan monitoring diperbarui.');
    }

    public function destroy(Request $request, Monitoring $monitoring)
    {
        if ($monitoring->foto_dokumentasi) {
            Storage::disk('public')->delete($monitoring->foto_dokumentasi);
        }

        $monitoring->delete();

        return $this->ok($request, 'Data monitoring dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'id_kegiatan'        => ['required', 'integer', 'exists:kegiatan_rehabilitasi,id_kegiatan'],
            'tanggal_monitoring' => ['nullable', 'date', 'before_or_equal:today'],
            'progres_persen'     => ['required', 'integer', 'between:0,100'],
            'kondisi_lahan'      => ['required', 'in:'.implode(',', config('lahan.kondisi'))],
            'catatan'            => ['nullable', 'string', 'max:2000'],
            'foto_dokumentasi'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto_dokumentasi')
            ? $request->file('foto_dokumentasi')->store('monitoring', 'public')
            : null;
    }
}
