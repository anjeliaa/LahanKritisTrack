<?php

namespace App\Http\Controllers;

use App\Models\KegiatanRehabilitasi;
use App\Models\Monitoring;
use App\Support\Catalog;
use App\Support\Ui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MonitoringController extends InternalController
{
    /** Halaman Catat Monitoring (khusus Petugas, dijaga oleh route). */
    public function create(Request $request)
    {
        return $this->halaman($request, 'monitoring.form', ['item' => null]);
    }

    /** Halaman Ubah Monitoring (Admin semua; Petugas hanya laporan yang ia catat). */
    public function edit(Request $request, Monitoring $monitoring)
    {
        $this->pastikanBolehMengubah($request, $monitoring);

        return $this->halaman($request, 'monitoring.form', ['item' => $monitoring]);
    }

    /** Halaman Riwayat Monitoring satu kegiatan. */
    public function riwayat(Request $request, KegiatanRehabilitasi $kegiatan)
    {
        $user = $request->user();
        $kembali = redirect()->route($user->isAdmin() ? 'admin.monitoring' : 'petugas.monitoring');

        // Baris ringkasan kegiatan ini, dari data yang sama dengan tabel monitoring (sudah sesuai hak akses).
        $k = Catalog::kegiatan()->firstWhere('id', $kegiatan->getKey());
        if (! $k) {
            return $kembali;
        }

        $baris = Catalog::monitoringRingkas(Catalog::monitoring($user))
            ->first(fn ($m) => $m['kegiatan'] === $k['nama'] && (string) $m['id_lahan'] === (string) $k['id_lahan']);

        if (! $baris) {
            return $kembali; // belum ada catatan (atau semuanya sudah dihapus)
        }

        $riwayat = $baris['riwayat'] ?? [];
        $riwayat = is_array($riwayat) ? $riwayat : (json_decode((string) $riwayat, true) ?: []);

        return $this->halaman($request, 'monitoring.riwayat', ['baris' => $baris, 'riwayat' => $riwayat]);
    }

    /** Hanya Petugas yang mencatat monitoring (route dibatasi role petugas). */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $kegiatan = $this->kegiatanBolehDicatat($request, (int) $data['id_kegiatan']);

        $data['id_user'] = $request->user()->id_user;
        $data['tanggal_monitoring'] ??= today()->toDateString();
        $data['foto_dokumentasi'] = $this->simpanFoto($request);

        Monitoring::create($data);

        $kegiatan->selaraskanStatus(); // progres 100% -> Selesai, progres > 0 -> Berjalan

        return $this->kembaliKeDaftar($request, 'Laporan monitoring disimpan.');
    }

    public function update(Request $request, Monitoring $monitoring)
    {
        $this->pastikanBolehMengubah($request, $monitoring);

        $data = $this->validated($request);
        $kegiatanBaru = $this->kegiatanBolehDicatat($request, (int) $data['id_kegiatan']);
        $idKegiatanLama = $monitoring->id_kegiatan;

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

        $kegiatanBaru->selaraskanStatus();
        if ($idKegiatanLama !== $kegiatanBaru->id_kegiatan) {
            KegiatanRehabilitasi::find($idKegiatanLama)?->selaraskanStatus();
        }

        return $this->kembaliKeDaftar($request, 'Laporan monitoring diperbarui.');
    }

    public function destroy(Request $request, Monitoring $monitoring)
    {
        if ($monitoring->foto_dokumentasi) {
            Storage::disk('public')->delete($monitoring->foto_dokumentasi);
        }

        $kegiatan = $monitoring->kegiatan;
        $monitoring->delete();

        $kegiatan?->selaraskanStatus();

        return $this->ok($request, 'Data monitoring dihapus.');
    }

    /** Menyusun halaman dengan kerangka internal untuk Admin maupun Petugas. */
    private function halaman(Request $request, string $view, array $extra)
    {
        $user = $request->user();
        $this->role = $user->isAdmin() ? 'admin' : 'petugas';

        $kegiatan = $user->isAdmin() ? Catalog::kegiatan() : Catalog::kegiatan(userId: $user->id_user);
        $monitoring = Catalog::monitoring($user);

        if ($user->isAdmin()) {
            $membaik = $monitoring->where('kondisi', 'Membaik')->count();
            $stats = [
                Ui::tile('Total Sesi', $monitoring->count(), 'Pemantauan', 'white'),
                Ui::tile('Rata-rata Progres', round($kegiatan->avg('progres') ?? 0) . '%', 'Semua kegiatan', 'green'),
                Ui::tile('Petugas Aktif', Monitoring::whereNotNull('id_user')->distinct()->count('id_user'), 'Mencatat monitoring', 'amber'),
                Ui::tile('Kondisi', ($monitoring->count() ? round($membaik / $monitoring->count() * 100) : 0) . '%', 'Membaik', 'blue'),
            ];
        } else {
            $stats = [
                Ui::tile('Total Sesi', $monitoring->count(), 'Pemantauan', 'white'),
                Ui::tile('Rata-rata Progres', round($kegiatan->avg('progres') ?? 0) . '%', 'Kegiatan Anda', 'green'),
                Ui::tile('Terpantau', $monitoring->pluck('id_kegiatan')->unique()->count(), 'Kegiatan', 'amber'),
            ];
        }

        return $this->render($view, 'monitoring', $this->role . '.monitoring', $stats, ['kegiatan' => $kegiatan] + $extra);
    }

    /** Setelah simpan: kembali ke daftar monitoring (bukan ke halaman form). */
    private function kembaliKeDaftar(Request $request, string $pesan)
    {
        $request->session()->flash('success', $pesan);

        return $request->expectsJson()
            ? response()->json(['message' => $pesan])
            : redirect()->route($request->user()->isAdmin() ? 'admin.monitoring' : 'petugas.monitoring');
    }

    /** Admin boleh mengubah semua; petugas hanya laporan yang ia catat sendiri. */
    private function pastikanBolehMengubah(Request $request, Monitoring $monitoring): void
    {
        abort_unless(
            $request->user()->isAdmin() || $monitoring->id_user === $request->user()->id_user,
            403,
            'Anda hanya dapat mengubah laporan monitoring yang Anda catat sendiri.'
        );
    }

    /** Petugas hanya boleh mencatat monitoring untuk kegiatan yang menjadi tanggung jawabnya. */
    private function kegiatanBolehDicatat(Request $request, int $id): KegiatanRehabilitasi
    {
        $kegiatan = KegiatanRehabilitasi::findOrFail($id);

        if (! $kegiatan->bolehDikelola($request->user())) {
            throw ValidationException::withMessages([
                'id_kegiatan' => 'Kegiatan ini bukan tanggung jawab Anda. Pilih kegiatan yang Anda kelola.',
            ]);
        }

        return $kegiatan;
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