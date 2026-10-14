<?php

namespace App\Http\Controllers;

use App\Support\DummyData;

/**
 * Basis untuk halaman internal (Admin & Petugas).
 * Saat backend siap, ganti DummyData::all() dengan query Eloquent.
 */
abstract class InternalController extends Controller
{
    protected string $role;

    protected function render(string $view, string $active, string $headerKey, array $extra = [])
    {
        $d = DummyData::all();

        return view($view, array_merge([
            'role'        => $this->role,
            'tabs'        => $d['tabs'][$this->role],
            'active'      => $active,
            'ph'          => $d['headers'][$headerKey],
            'kecamatan'   => $d['kecamatan'],
            'kegiatan'    => $d['kegiatan'],
            'monitoring'  => $d['monitoring'],
            'status_opts' => $d['status_opts'],
            'kritis_opts' => $d['kritis_opts'],
            'app'         => [
                'kecamatan' => $d['kecamatan'],
                'kegiatan'  => $d['kegiatan'],
                'dashboard' => $d['dashboard'],
            ],
        ], $extra));
    }
}
