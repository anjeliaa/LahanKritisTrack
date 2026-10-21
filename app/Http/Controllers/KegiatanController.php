<?php

namespace App\Http\Controllers;

use App\Models\KegiatanRehabilitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KegiatanController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = $request->user();

        // Petugas: kegiatan yang ia buat menjadi tanggung jawabnya. Admin: menugaskan petugas (boleh dikosongkan).
        $data['id_user'] = $user->isAdmin() ? ($data['id_user'] ?? null) : $user->id_user;

        // Kegiatan baru belum punya monitoring (progres 0%) -> hanya Rencana atau Berjalan.
        $status = $data['status'] ?? 'Rencana';
        if (! in_array($status, ['Rencana', 'Berjalan'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Kegiatan baru belum memiliki progres, status hanya dapat Rencana atau Berjalan. Status Selesai terisi otomatis saat progres monitoring mencapai 100%.',
            ]);
        }
        $data['status'] = $status;
        $data['tanggal_status'] = today();

        KegiatanRehabilitasi::create($data);

        return $this->ok($request, 'Kegiatan rehabilitasi disimpan.');
    }

    public function update(Request $request, KegiatanRehabilitasi $kegiatan)
    {
        $this->pastikanBolehDikelola($request, $kegiatan);

        $data = $this->validated($request);

        // Hanya Admin yang dapat memindahkan penanggung jawab.
        if (! $request->user()->isAdmin()) {
            unset($data['id_user']);
        }

        if (isset($data['status']) && $data['status'] !== $kegiatan->status) {
            $this->pastikanStatusSesuai($kegiatan, $data['status']);
            $data['tanggal_status'] = today();
        }

        $kegiatan->update($data);

        // status berubah menjadi Selesai lewat form: lengkapi tanggal selesai bila kosong
        if ($kegiatan->status === 'Selesai' && ! $kegiatan->tanggal_selesai) {
            $kegiatan->update(['tanggal_selesai' => $kegiatan->tanggal_status ?? today()]);
        }

        return $this->ok($request, 'Perubahan kegiatan disimpan.');
    }

    /** Modal "Update Status Kegiatan". */
    public function status(Request $request)
    {
        $data = $request->validate([
            'kegiatan' => ['required', 'integer', 'exists:kegiatan_rehabilitasi,id_kegiatan'],
            'status'   => ['required', 'in:'.implode(',', config('lahan.status'))],
        ]);

        $kegiatan = KegiatanRehabilitasi::with('monitoringTerakhir')->findOrFail($data['kegiatan']);
        $this->pastikanBolehDikelola($request, $kegiatan);

        if ($kegiatan->status !== $data['status']) {
            $this->pastikanStatusSesuai($kegiatan, $data['status']);
            $kegiatan->terapkanStatus($data['status']);
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

    private function pastikanBolehDikelola(Request $request, KegiatanRehabilitasi $kegiatan): void
    {
        abort_unless(
            $kegiatan->bolehDikelola($request->user()),
            403,
            'Kegiatan ini bukan tanggung jawab Anda. Hanya petugas penanggung jawab atau Admin yang dapat mengubahnya.'
        );
    }

    /** Status harus sejalan dengan progres monitoring terbaru. */
    private function pastikanStatusSesuai(KegiatanRehabilitasi $kegiatan, string $baru): void
    {
        $kegiatan->loadMissing('monitoringTerakhir');
        $boleh = $kegiatan->statusDiizinkan();

        if (! in_array($baru, $boleh, true)) {
            $p = $kegiatan->progres;
            $pesan = match (true) {
                $baru === 'Selesai' => "Status Selesai hanya dapat dipilih bila progres terbaru 100% (saat ini {$p}%). Catat monitoring dengan progres 100% terlebih dahulu; status akan berubah otomatis.",
                $p >= 100           => 'Progres terbaru sudah 100%, sehingga status kegiatan adalah Selesai.',
                default             => "Progres terbaru {$p}% sehingga status yang sesuai adalah ".implode(' atau ', $boleh).'.',
            };

            throw ValidationException::withMessages(['status' => $pesan]);
        }
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
            'id_user'          => ['nullable', 'integer', Rule::exists('users', 'id_user')->where('role', 'petugas')],
        ], [
            'id_user.exists' => 'Petugas penanggung jawab harus akun dengan role Petugas.',
        ], ['id_user' => 'Petugas penanggung jawab']);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
