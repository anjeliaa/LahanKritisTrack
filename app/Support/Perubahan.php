<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\PerubahanDariAdmin;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/** Membandingkan data sebelum/sesudah diubah Admin, lalu memberi tahu Petugas terkait. */
class Perubahan
{
    /** Label yang hanya dicatat sebagai "diganti" (nilainya berupa path berkas, tidak informatif). */
    private const TANPA_NILAI = ['Foto dokumentasi'];

    /** Angka tanpa nol di belakang koma: 10 -> "10", 12.5 -> "12,5". */
    public static function angka(float|int|string|null $nilai): string
    {
        return rtrim(rtrim(number_format((float) $nilai, 2, ',', '.'), '0'), ',');
    }

    /**
     * @param  array<string, string>  $sebelum  label => nilai
     * @param  array<string, string>  $sesudah  label => nilai
     * @return array<int, array{label: string, lama: ?string, baru: ?string}>
     */
    public static function beda(array $sebelum, array $sesudah): array
    {
        $hasil = [];

        foreach ($sesudah as $label => $baru) {
            $lama = $sebelum[$label] ?? null;

            if ($lama === $baru) {
                continue;
            }

            $hasil[] = in_array($label, self::TANPA_NILAI, true)
                ? ['label' => $label, 'lama' => null, 'baru' => null]
                : [
                    'label' => $label,
                    'lama'  => Str::limit((string) ($lama ?? '-'), 100),
                    'baru'  => Str::limit((string) ($baru ?? '-'), 100),
                ];
        }

        return $hasil;
    }

    /**
     * Kirim pemberitahuan ke Petugas yang terkait bila ada yang benar-benar berubah.
     *
     * @param  array<int, int|null>  $idPenerima  id_user Petugas terkait (boleh ada yang kosong/ganda)
     */
    public static function beritahu(string $jenis, array $sebelum, array $sesudah, string $kegiatan, array $idPenerima, User $admin): void
    {
        $perubahan = self::beda($sebelum, $sesudah);

        if ($perubahan === []) {
            return;
        }

        $penerima = User::where('role', 'petugas')
            ->whereIn('id_user', array_values(array_unique(array_filter($idPenerima))))
            ->where('id_user', '!=', $admin->id_user)
            ->get();

        if ($penerima->isEmpty()) {
            return;
        }

        Notification::send($penerima, new PerubahanDariAdmin($jenis, $kegiatan, $admin->nama, $perubahan));
    }
}
