<?php

namespace App\Support;

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;

class KnowledgeBase
{
    /**
     * Pengetahuan fitur aplikasi dan sumber data yang boleh digunakan
     * oleh chatbot masyarakat.
     */
    private function documents(): array
    {
        return [
            [
                'title' => 'Tentang LahanKritisTrack',
                'keywords' => ['aplikasi', 'lahankritistrack', 'sistem', 'fungsi', 'tujuan'],
                'content' => 'LahanKritisTrack adalah sistem informasi peta interaktif dan monitoring program rehabilitasi lahan kritis di Kota Bengkulu. Sistem membantu pengguna melihat sebaran lahan kritis serta informasi kegiatan rehabilitasi dan perkembangannya.'
            ],
            [
                'title' => 'Peta Sebaran Saat Ini',
                'keywords' => ['peta', 'sebaran', 'warna', 'legenda', 'lokasi', 'petak'],
                'content' => 'Peta Sebaran Saat Ini menampilkan petak lahan berdasarkan kelas kekritisan. Pengguna dapat melihat informasi petak dengan memilih atau mengeklik area pada peta. Warna mengikuti legenda kelas kekritisan dan status rehabilitasi yang ditampilkan aplikasi.'
            ],
            [
                'title' => 'Visualisasi Temporal',
                'keywords' => ['temporal', 'tahun', 'perubahan', 'perbandingan', 'simulasi', '2022', '2023', '2024', '2025', '2026'],
                'content' => 'Visualisasi Temporal digunakan untuk membandingkan tampilan kondisi petak lahan antara dua tahun yang dipilih. Warna menunjukkan perubahan kondisi pada visualisasi, seperti membaik, memburuk, atau tetap. Data tahun 2022 sampai 2025 merupakan skenario simulasi untuk visualisasi, bukan catatan historis hasil pengukuran sebenarnya. Tahun 2026 menggunakan data dasar aplikasi. Hasil perbandingan tidak boleh dianggap sebagai bukti perubahan historis di lapangan.'
            ],
            [
                'title' => 'Filter Peta',
                'keywords' => ['filter', 'kecamatan', 'pencarian', 'cari', 'kelas'],
                'content' => 'Pengguna dapat menggunakan pencarian dan filter kecamatan untuk mempersempit informasi yang ditampilkan pada peta. Filter membantu pengguna menemukan petak berdasarkan wilayah atau informasi yang tersedia pada aplikasi.'
            ],
            [
                'title' => 'Kegiatan Rehabilitasi',
                'keywords' => ['kegiatan', 'rehabilitasi', 'program', 'target', 'realisasi', 'progres', 'status'],
                'content' => 'Informasi kegiatan rehabilitasi mencakup nama kegiatan, jenis kegiatan, lokasi petak, target luas, tanggal kegiatan, status, dan progres. Progres kegiatan mengacu pada catatan monitoring terbaru. Realisasi luas dihitung berdasarkan target luas dikalikan persentase progres.'
            ],
            [
                'title' => 'Monitoring',
                'keywords' => ['monitoring', 'pemantauan', 'kondisi', 'catatan', 'dokumentasi', 'foto'],
                'content' => 'Monitoring merupakan pencatatan perkembangan kegiatan rehabilitasi. Informasi monitoring dapat mencakup tanggal pemantauan, persentase progres, kondisi lahan, catatan, dan dokumentasi foto. Catatan monitoring terbaru menjadi dasar tampilan progres kegiatan.'
            ],
            [
                'title' => 'Membaca kelas kekritisan',
                'keywords' => ['kritis', 'kekritisan', 'ringan', 'sedang', 'berat', 'sangat berat', 'pulih', 'direhabilitasi'],
                'content' => 'Kelas kekritisan menunjukkan kategori kondisi lahan berdasarkan data yang tersedia pada aplikasi. Petak yang memiliki kegiatan rehabilitasi berstatus selesai dapat ditampilkan sebagai telah direhabilitasi sesuai aturan tampilan peta. Status telah direhabilitasi pada aplikasi tidak otomatis berarti seluruh kondisi ekologis lahan telah pulih.'
            ],
        ];
    }

    /**
     * Mengambil dokumen fitur dan data database yang relevan dengan pertanyaan.
     */
    public function retrieve(string $question): string
    {
        $normalized = mb_strtolower($question);

        $context = [];

        // Retrieval pengetahuan fitur aplikasi.
        foreach ($this->documents() as $document) {
            foreach ($document['keywords'] as $keyword) {
                if (str_contains($normalized, mb_strtolower($keyword))) {
                    $context[] = $document['title'].": ".$document['content'];
                    break;
                }
            }
        }

        // Selalu sertakan penjelasan umum agar pertanyaan umum tetap terjawab.
        if (empty($context)) {
            $context[] = $this->documents()[0]['title'].": ".$this->documents()[0]['content'];
        }

        // Retrieval kegiatan rehabilitasi yang sudah dipublikasikan.
        if ($this->asksAboutActivities($normalized)) {
            $context[] = $this->retrieveActivities($question);
        }

        // Retrieval data lahan.
        if ($this->asksAboutLand($normalized)) {
            $context[] = $this->retrieveLand($question);
        }

        return implode("\n\n", array_filter($context));
    }

    private function asksAboutActivities(string $question): bool
    {
        return $this->containsAny($question, [
            'kegiatan', 'rehabilitasi', 'program', 'progres',
            'monitoring', 'pemantauan', 'target', 'realisasi',
            'selesai', 'berjalan', 'rencana', 'catatan',
        ]);
    }

    private function asksAboutLand(string $question): bool
    {
        return $this->containsAny($question, [
            'lahan', 'petak', 'kritis', 'kekritisan', 'luas',
            'kecamatan', 'wilayah', 'sebaran', 'direhabilitasi',
        ]);
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mengambil kegiatan publik. Nama petugas dan informasi internal
     * tidak disertakan dalam konteks Gemini.
     */
    private function retrieveActivities(string $question): string
    {
        $query = KegiatanRehabilitasi::query()
            ->published()
            ->with([
                'lahan',
                'monitoringTerakhir',
                'monitoring' => fn ($q) => $q
                    ->orderByDesc('tanggal_monitoring')
                    ->orderByDesc('id_monitoring')
                    ->limit(3),
            ])
            ->orderByDesc('id_kegiatan');

        $terms = $this->searchTerms($question);

        if (! empty($terms)) {
            $query->where(function ($where) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.$term.'%';

                    $where->orWhere('nama_kegiatan', 'like', $like)
                        ->orWhere('jenis_kegiatan', 'like', $like)
                        ->orWhereHas('lahan', fn ($lahan) => $lahan
                            ->where('kecamatan', 'like', $like)
                            ->orWhere('kritis', 'like', $like));
                }
            });
        }

        $activities = $query->limit(8)->get();

        // Jika pencarian spesifik tidak menemukan hasil, jangan mengambil
        // kegiatan lain secara acak dan menganggapnya sebagai jawaban.
        if ($activities->isEmpty()) {
            return "DATA KEGIATAN: Tidak ditemukan kegiatan publik yang cocok dengan kata kunci pertanyaan.";
        }

        $rows = $activities->map(function ($activity) {
            $land = $activity->lahan;
            $latest = $activity->monitoringTerakhir;

            $monitoring = $activity->monitoring->map(function ($item) {
                return sprintf(
                    'Tanggal: %s; progres: %s%%; kondisi: %s; catatan: %s',
                    $item->tanggal_monitoring?->format('d-m-Y') ?? '-',
                    $item->progres_persen ?? 0,
                    $item->kondisi_lahan ?: '-',
                    $item->catatan ?: '-'
                );
            })->implode(' | ');

            return sprintf(
                '- %s; jenis: %s; kecamatan: %s; petak: %s; kelas lahan: %s; target: %s Ha; progres terbaru: %s%%; realisasi: %s Ha; status: %s; monitoring terbaru: %s; riwayat monitoring: %s',
                $activity->nama_kegiatan,
                $activity->jenis_kegiatan ?: '-',
                $land?->kecamatan ?? '-',
                $activity->id_lahan ?? '-',
                $land?->kritis ?? '-',
                $activity->target_luas ?? 0,
                $latest?->progres_persen ?? 0,
                $activity->realisasi,
                $activity->status,
                $latest?->tanggal_monitoring?->format('d-m-Y') ?? 'Belum ada',
                $monitoring ?: 'Belum ada catatan monitoring'
            );
        });

        return "DATA KEGIATAN REHABILITASI YANG DIPUBLIKASIKAN:\n".$rows->implode("\n");
    }

    /**
     * Mengambil ringkasan luas dan jumlah petak berdasarkan kecamatan
     * dan kelas kekritisan.
     */
    private function retrieveLand(string $question): string
    {
        $query = DataLahan::query()
            ->select('kecamatan', 'kritis')
            ->selectRaw('COUNT(*) as jumlah_petak')
            ->selectRaw('SUM(luas_ha) as total_luas')
            ->groupBy('kecamatan', 'kritis')
            ->orderBy('kecamatan');

        $terms = $this->searchTerms($question);

        if (! empty($terms)) {
            $query->where(function ($where) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.$term.'%';

                    $where->orWhere('kecamatan', 'like', $like)
                        ->orWhere('kritis', 'like', $like);
                }
            });
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return "DATA LAHAN: Tidak ditemukan data lahan yang cocok dengan kata kunci pertanyaan.";
        }

        $result = $rows->map(fn ($row) => sprintf(
            '- Kecamatan %s, kelas %s: %s petak, luas %.2f Ha',
            $row->kecamatan,
            $row->kritis,
            $row->jumlah_petak,
            $row->total_luas
        ))->implode("\n");

        return "RINGKASAN DATA LAHAN:\n".$result;
    }

    private function searchTerms(string $question): array
    {
        $stopWords = [
            'yang', 'dan', 'atau', 'apa', 'berapa', 'bagaimana',
            'dimana', 'kapan', 'dengan', 'untuk', 'pada', 'dari',
            'tentang', 'tolong', 'jelaskan', 'sebutkan', 'apakah',
            'saya', 'ingin', 'lihat', 'informasi', 'data', 'di',
            'ke', 'ini', 'itu', 'ada', 'the', 'berapa',
        ];

        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($question));

        return array_values(array_unique(array_filter(
            $words ?: [],
            fn ($word) => mb_strlen($word) >= 3
                && ! in_array($word, $stopWords, true)
        )));
    }
}
