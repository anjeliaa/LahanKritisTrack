<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\Catalog;
use App\Support\Fmt;
use App\Support\LahanImporter;
use App\Support\Ui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LahanController extends InternalController
{
    /** Halaman Tambah Petak (gambar polygon pada peta atau tempel GeoJSON). */
    public function create()
    {
        return $this->halaman('lahan.form', ['item' => null]);
    }

    /** Halaman Ubah Petak. */
    public function edit(DataLahan $lahan)
    {
        return $this->halaman('lahan.form', ['item' => $lahan]);
    }

    /** Halaman Impor GeoJSON. */
    public function formImpor()
    {
        return $this->halaman('lahan.import');
    }

    /** Halaman Detail Petak beserta kegiatan rehabilitasi di dalamnya. */
    public function show(DataLahan $lahan)
    {
        $row = Catalog::lahanRow(DataLahan::denganRehabilitasi()->findOrFail($lahan->getKey()));
        $kegiatan = Catalog::kegiatan();

        return $this->halaman('lahan.show', [
            'item'           => $lahan,
            'row'            => $row,
            'kegiatan'       => $kegiatan,
            'kegiatan_lahan' => $kegiatan->where('id_lahan', $lahan->getKey())->values(),
        ]);
    }

    /** Tambah petak lahan secara manual (gambar polygon pada peta atau tempel GeoJSON). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kecamatan_id' => ['required', 'integer', Rule::in(array_keys(config('lahan.kecamatan')))],
            'wilayah'      => ['nullable', 'string', 'max:255'],
            'bpdas'        => ['required', 'string', 'max:255'],
            'kritis'       => ['required', Rule::in(array_values(config('lahan.kritis')))],
            'luas_ha'      => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'geometri'     => ['required', 'string'],
        ], [
            'geometri.required' => 'Gambar area petak pada peta (minimal 3 titik) atau tempel GeoJSON.',
        ]);

        try {
            $geom = LahanImporter::normalizeGeometry($data['geometri']);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['geometri' => $e->getMessage()]);
        }

        $lahan = DataLahan::create([
            'wilayah'      => ($data['wilayah'] ?? null) ?: 'Kota Bengkulu',
            'kecamatan_id' => $data['kecamatan_id'],
            'kecamatan'    => config("lahan.kecamatan.{$data['kecamatan_id']}.nama"),
            'bpdas'        => $data['bpdas'],
            'kritis'       => $data['kritis'],
            // luas kosong -> dihitung dari bentuk polygon
            'luas_ha'      => ($data['luas_ha'] ?? null) !== null && $data['luas_ha'] !== '' ? round((float) $data['luas_ha'], 2) : round(LahanImporter::areaHa($geom), 2),
            'geometri'     => $geom,
        ]);

        return $this->kembaliKeDaftar($request, "Petak lahan #{$lahan->id_lahan} ditambahkan.");
    }

    public function update(Request $request, DataLahan $lahan)
    {
        $data = $request->validate([
            'kecamatan_id' => ['required', 'integer', Rule::in(array_keys(config('lahan.kecamatan')))],
            'wilayah'      => ['nullable', 'string', 'max:255'],
            'bpdas'        => ['required', 'string', 'max:255'],
            'kritis'       => ['required', Rule::in(array_values(config('lahan.kritis')))],
            'luas_ha'      => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $data['kecamatan'] = config("lahan.kecamatan.{$data['kecamatan_id']}.nama");

        $lahan->update($data);

        return $this->kembaliKeDaftar($request, "Data petak lahan #{$lahan->id_lahan} diperbarui.");
    }

    public function destroy(Request $request, DataLahan $lahan)
    {
        $jml = $lahan->kegiatan()->count();

        if ($jml > 0) {
            throw ValidationException::withMessages([
                'lahan' => "Petak #{$lahan->id_lahan} tidak dapat dihapus karena masih memiliki {$jml} kegiatan rehabilitasi.",
            ]);
        }

        $lahan->delete();

        // Hapus lewat popup (AJAX)
        return $this->ok($request, "Petak lahan #{$lahan->id_lahan} dihapus.");
    }

    /** Impor GeoJSON: petak dengan OBJECTID yang sama diperbarui, sisanya ditambahkan. */
    public function import(Request $request)
    {
        $request->validate([
            'geojson' => ['required', 'file', 'extensions:geojson,json', 'max:10240'],
        ], [
            'geojson.required'   => 'Pilih berkas GeoJSON terlebih dahulu.',
            'geojson.extensions' => 'Berkas harus berekstensi .geojson atau .json.',
            'geojson.max'        => 'Ukuran berkas maksimal 10 MB.',
        ]);

        try {
            $r = DB::transaction(fn () => LahanImporter::fromJson(file_get_contents($request->file('geojson')->getRealPath())));
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['geojson' => $e->getMessage()]);
        }

        if ($r['created'] + $r['updated'] === 0) {
            throw ValidationException::withMessages([
                'geojson' => 'Tidak ada fitur yang dapat diimpor. '.implode(' ', array_slice($r['errors'], 0, 3)),
            ]);
        }

        if ($r['skipped'] > 0) {
            $request->session()->flash('error', "{$r['skipped']} fitur dilewati. ".implode(' ', array_slice($r['errors'], 0, 2)));
        }

        return $this->kembaliKeDaftar($request, "Impor selesai: {$r['created']} petak ditambahkan, {$r['updated']} diperbarui.");
    }

    /** Menyusun halaman dengan kerangka internal (khusus Admin, dijaga oleh route). */
    private function halaman(string $view, array $extra = [])
    {
        $this->role = 'admin';

        $semua = DataLahan::all(['id_lahan', 'kecamatan_id', 'kritis', 'luas_ha']);
        $kritisTinggi = $semua->whereIn('kritis', [config('lahan.kritis.kritis'), config('lahan.kritis.sangat')])->count();

        return $this->render($view, 'lahan', 'admin.lahan', [
            Ui::tile('Petak Lahan', $semua->count(), $semua->pluck('kecamatan_id')->unique()->count().' kecamatan', 'white'),
            Ui::tile('Total Luas', Fmt::ha($semua->sum('luas_ha')), 'Hektare', 'amber'),
            Ui::tile('Kritis', $kritisTinggi, 'Petak (kritis & sangat kritis)', 'red'),
            Ui::tile('Potensial', $semua->where('kritis', config('lahan.kritis.potensial'))->count(), 'Petak', 'green'),
        ], $extra + [
            'kegiatan'        => collect(),
            'kecamatan_semua' => collect(config('lahan.kecamatan'))->map(fn ($k, $id) => ['id' => $id] + $k)->values(),
        ]);
    }

    /** Setelah simpan: kembali ke daftar data lahan. */
    private function kembaliKeDaftar(Request $request, string $pesan)
    {
        $request->session()->flash('success', $pesan);

        return $request->expectsJson()
            ? response()->json(['message' => $pesan])
            : redirect()->route('admin.lahan');
    }
}