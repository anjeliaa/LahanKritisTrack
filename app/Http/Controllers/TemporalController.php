<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemporalController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil daftar tahun yang tersedia
        $tahunList = DB::table('data_lahan_temporal')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun');

        // Tahun yang dipilih, default tahun terbaru
        $tahun = $request->input(
            'tahun',
            $tahunList->max() ?? 2026
        );

        // Sumber data: asli atau dummy
        $sumber = $request->input('sumber', 'dummy');

        if (!in_array($sumber, ['asli', 'dummy'])) {
            $sumber = 'dummy';
        }

        // Mengambil data berdasarkan tahun dan sumber
        $data = DB::table('data_lahan_temporal')
            ->where('tahun', $tahun)
            ->where('data_source', $sumber)
            ->get();

        // Rekap luas lahan berdasarkan kategori kekritisan
        $rekap = DB::table('data_lahan_temporal')
            ->select('kritis', DB::raw('SUM(luas_ha) as total_luas'))
            ->where('tahun', $tahun)
            ->where('data_source', $sumber)
            ->groupBy('kritis')
            ->get();

        // Total luas keseluruhan
        $totalLuas = $data->sum('luas_ha');

        // Jumlah petak lahan
        $jumlahLahan = $data->count();

        // Data grafik temporal
        $grafik = DB::table('data_lahan_temporal')
            ->select(
                'tahun',
                DB::raw('SUM(luas_ha) as total_luas')
            )
            ->where('data_source', $sumber)
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get();

        return view('temporal.index', compact(
            'tahunList',
            'tahun',
            'sumber',
            'data',
            'rekap',
            'totalLuas',
            'jumlahLahan',
            'grafik'
        ));
    }
}
