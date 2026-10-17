<?php

namespace App\Http\Controllers;

use App\Models\KegiatanRehabilitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['id_user'] = $request->user()->id_user;
        $data['status'] = $data['status'] ?? 'Rencana';
        $data['tanggal_status'] = today();

        KegiatanRehabilitasi::create($data);

        return $this->ok($request, 'Kegiatan rehabilitasi disimpan.');
    }

    public function update(Request $request, KegiatanRehabilitasi $kegiatan)
    {
        $data = $this->validated($request);

        if (isset($data['status']) && $data['status'] !== $kegiatan->status) {
            $data['tanggal_status'] = today();
        }

        $kegiatan->update($data);

        return $this->ok($request, 'Perubahan kegiatan disimpan.');
    }

    /** Modal "Update Status Kegiatan". */
    public function status(Request $request)
    {
        $data = $request->validate([
            'kegiatan' => ['required', 'integer', 'exists:kegiatan_rehabilitasi,id_kegiatan'],
            'status'   => ['required', 'in:'.implode(',', config('lahan.status'))],
        ], [], ['kegiatan' => 'kegiatan']);

        $kegiatan = KegiatanRehabilitasi::findOrFail($data['kegiatan']);

        if ($kegiatan->status !== $data['status']) {
            $kegiatan->update(['status' => $data['status'], 'tanggal_status' => today()]);
        }

        return $this->ok($request, 'Status kegiatan diperbarui.');
    }

    public function destroy(Request $request, KegiatanRehabilitasi $kegiatan)
    {
        $foto = $kegiatan->monitoring()->whereNotNull('foto_dokumentasi')->pluck('foto_dokumentasi');

        $kegiatan->delete(); // monitoring ikut terhapus (cascade)

        Storage::disk('public')->delete($foto->all());

        return $this->ok($request, 'Kegiatan beserta riwayat monitoringnya dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama_kegiatan'    => ['required', 'string', 'max:255'],
            'id_lahan'         => ['required', 'integer', 'exists:data_lahan,id_lahan'],
            'jenis_kegiatan'   => ['nullable', 'string', 'max:255'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'target_luas'      => ['required', 'numeric', 'min:0', 'max:9999999'],
            'tanggal_mulai'    => ['nullable', 'date'],
            'tanggal_selesai'  => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status'           => ['nullable', 'in:'.implode(',', config('lahan.status'))],
            'is_published'     => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
