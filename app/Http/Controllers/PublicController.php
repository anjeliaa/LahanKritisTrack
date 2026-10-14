<?php

namespace App\Http\Controllers;

use App\Support\DummyData;

class PublicController extends Controller
{
    public function landing()
    {
        $d = DummyData::all();

        return view('pages.landing', [
            'landing'     => $d['landing'],
            'kecamatan'   => $d['kecamatan'],
            'publik'      => $d['publik'],
            'kritis_opts' => $d['kritis_opts'],
            'status_opts' => $d['status_opts'],
            'app'         => ['kecamatan' => $d['kecamatan'], 'kegiatan' => $d['kegiatan']],
        ]);
    }
}
