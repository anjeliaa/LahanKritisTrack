<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\Catalog;
use App\Support\Fmt;
use App\Support\Ui;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PetugasController extends InternalController
{
    protected string $role = 'petugas';

    public function peta()
    {
        $kegiatan = Catalog::kegiatan();

        $kritisTinggi = DataLahan::whereIn('kritis', [
            config('lahan.kritis.kritis'),
            config('lahan.kritis.sangat'),
        ])->count();

        return $this->render(
            'petugas.peta',
            'peta',
            'petugas.peta',
            [
                Ui::tile('Petak Lahan', DataLahan::count(), 'Terdata', 'white'),
                Ui::tile('Total Luas', Fmt::ha(DataLahan::sum('luas_ha')), 'Hektare', 'amber'),
                Ui::tile('Kritis', $kritisTinggi, 'Petak Lahan (kritis)', 'red'),
            ],
            ['kegiatan' => $kegiatan]
        );
    }

    public function kegiatan()
    {
        $kegiatan = Catalog::kegiatan();

        [$total, $berjalan, $selesai] = $this->hitungStatus($kegiatan);

        return $this->render(
            'petugas.kegiatan',
            'kegiatan',
            'petugas.kegiatan',
            [
                Ui::tile('Total Kegiatan', $total, 'Program', 'white'),
                Ui::tile('Berjalan', $berjalan, 'Perlu update', 'green'),
                Ui::tile('Selesai', $selesai, 'Tuntas', 'blue'),
            ],
            ['kegiatan' => $kegiatan]
        );
    }

    public function monitoring()
    {
        $kegiatan = Catalog::kegiatan();
        $monitoring = Catalog::monitoring(auth()->user());

        return $this->render(
            'petugas.monitoring',
            'monitoring',
            'petugas.monitoring',
            [
                Ui::tile('Total Sesi', $monitoring->count(), 'Pemantauan', 'white'),
                Ui::tile(
                    'Rata-rata Progres',
                    round($kegiatan->avg('progres') ?? 0) . '%',
                    'Semua kegiatan',
                    'green'
                ),
                Ui::tile(
                    'Terpantau',
                    $monitoring->pluck('id_kegiatan')->unique()->count(),
                    'Kegiatan',
                    'amber'
                ),
            ],
            [
                'kegiatan' => $kegiatan,
                'monitoring' => $monitoring,
            ]
        );
    }

    // ---------- Profil Petugas ----------
    public function profil()
    {
        return $this->render(
            'petugas.profil',
            'profil',
            'petugas.profil',
            [],
            [
                'user' => auth()->user(),
            ]
        );
    }

    // ---------- Perbarui Profil Petugas ----------
    public function updateProfil(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($user->id_user, 'id_user'),
            ],

            'password_lama' => [
                'nullable',
                'required_with:password_baru',
                'current_password',
            ],

            'password_baru' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'required_with:password_lama',
            ],
        ]);

        $user->nama = $data['nama'];
        $user->username = $data['username'];

        if (!empty($data['password_baru'])) {
            $user->password = $data['password_baru'];
        }

        $user->save();

        return redirect()
            ->route('petugas.profil')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
