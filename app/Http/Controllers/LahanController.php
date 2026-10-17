<?php

namespace App\Http\Controllers;

use App\Models\DataLahan;
use App\Support\LahanImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LahanController extends Controller
{
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

        return $this->ok($request, "Data petak lahan #{$lahan->id_lahan} diperbarui.");
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

        return $this->ok($request, "Impor selesai: {$r['created']} petak ditambahkan, {$r['updated']} diperbarui.");
    }
}
