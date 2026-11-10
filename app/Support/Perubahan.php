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

    /** Tanda untuk baris catatan monitoring yang ditambahkan Admin (agar tidak tercampur dengan catatan Petugas). */
    public const TAG_ADMIN = '(Admin): ';

    /**
     * Saat Admin menyimpan catatan monitoring, baris yang baru ditambahkan diberi tanda "(Admin): ".
     * Baris catatan Petugas (yang sudah ada sebelumnya) dibiarkan apa adanya, dan baris yang sudah
     * bertanda (Admin) tidak diberi tanda dua kali. Bila Admin menambah kalimat di ujung baris Petugas,
     * tambahannya dipindah ke baris baru bertanda (Admin).
     */
    public static function tandaiCatatanAdmin(?string $lama, ?string $baru): ?string
    {
        if ($baru === null || trim($baru) === '') {
            return $baru;
        }

        $lamaBaris = array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) $lama)),
            fn ($b) => $b !== ''
        ));

        $hasil = [];

        foreach (preg_split('/\R/', $baru) as $baris) {
            $b = trim($baris);

            if ($b === '' || in_array($b, $lamaBaris, true) || stripos($b, '(admin)') === 0) {
                $hasil[] = $b;
                continue;
            }

            // baris Petugas yang ditambah kalimat di ujungnya -> pisahkan tambahannya
            $dasar = null;
            foreach ($lamaBaris as $o) {
                if (! str_starts_with($b, $o)) {
                    continue;
                }
                $sisa = substr($b, strlen($o));
                if ($sisa !== '' && (preg_match('/^\s/u', $sisa) || preg_match('/[.!?;:,]$/u', $o))
                    && ($dasar === null || strlen($o) > strlen($dasar))) {
                    $dasar = $o;
                }
            }

            if ($dasar !== null) {
                $hasil[] = $dasar;
                $hasil[] = self::TAG_ADMIN.trim(substr($b, strlen($dasar)));
            } else {
                $hasil[] = self::TAG_ADMIN.$b;
            }
        }

        return implode("\n", $hasil);
    }

    /**
     * Bila perubahan catatan hanya berupa tambahan baris bertanda (Admin), kembalikan isi tambahannya
     * (tanpa tanda). Selain itu null, sehingga perubahan ditampilkan sebagai "lama -> baru".
     */
    public static function tambahanAdmin(string $lama, string $baru): ?string
    {
        $baris = fn (string $t) => array_values(array_filter(
            array_map('trim', preg_split('/\R/', $t === '-' ? '' : $t)),
            fn ($b) => $b !== ''
        ));

        $lamaB = $baris($lama);
        $baruB = $baris($baru);

        foreach ($lamaB as $b) {
            if (! in_array($b, $baruB, true)) {
                return null; // ada baris lama yang berubah atau hilang
            }
        }

        $tambah = array_values(array_diff($baruB, $lamaB));
        if ($tambah === []) {
            return null;
        }

        $isi = [];
        foreach ($tambah as $b) {
            if (stripos($b, '(admin)') !== 0) {
                return null;
            }
            $isi[] = trim(preg_replace('/^\(admin\)\s*:?\s*/i', '', $b));
        }

        return implode(' ', $isi);
    }

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

            // catatan: bila Admin hanya menambah baris bertanda (Admin), tampilkan tambahannya saja
            if ($label === 'Catatan' && ($tambahan = self::tambahanAdmin((string) ($lama ?? ''), (string) $baru)) !== null) {
                $hasil[] = ['label' => 'Catatan Admin', 'lama' => '-', 'baru' => Str::limit($tambahan, 100)];
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
