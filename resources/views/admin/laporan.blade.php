@extends('layouts.internal')
@section('title', 'Laporan — LahanKritisTrack')

@section('content')
<div class="card no-print" data-table-filter="rtbl">
    <div class="card-head">
        <h2>Laporan Kegiatan dan Monitoring Terpadu</h2>
        <div class="acts">
            <button type="button" class="btn btn-pdf" data-print><svg class="ic"><use href="#i-filetext"/></svg> Unduh PDF</button>
            <button type="button" class="btn btn-excel" data-toast="Laporan diunduh sebagai Excel (simulasi frontend)"><svg class="ic"><use href="#i-sheet"/></svg> Unduh Excel</button>
        </div>
    </div>
    <div class="toolbar">
        <select class="select tool-select tf-kec"><option value="">Semua Kecamatan</option>
            @foreach($kecamatan as $c)
            <option value="{{ $c['nama'] }}">{{ $c['nama'] }}</option>
            @endforeach
        </select>
        <select class="select tool-select"><option>Semua Periode</option><option>Semester I 2026</option><option>Semester II 2026</option></select>
        <select class="select tool-select tf-status"><option value="">Semua Status</option>
            @foreach($status_opts as $s)
            <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
    </div>
</div>

<article class="report">
    <div class="report-top"><span>Pratinjau laporan</span><span>Halaman 1 dari 1</span></div>
    <div class="report-body">
        <div class="kop">
            <div class="seal"><svg class="ic"><use href="#i-leaf"/></svg></div>
            <div class="c">
                <b>PEMERINTAH KOTA BENGKULU</b>
                <span>Sistem Informasi Peta Interaktif &amp; Program Rehabilitasi Lahan Kritis Track</span>
                <span>Kota Bengkulu, Provinsi Bengkulu</span>
            </div>
            <div class="no">NO. DOKUMEN<b>{{ $lap['nomor'] }}</b></div>
        </div>
        <h2 class="rt">REKAPITULASI PROGRAM REHABILITASI LAHAN KRITIS</h2>
        <div class="rp">{{ $lap['periode'] }}</div>

        <div class="table-wrap">
        <table class="rtbl" id="rtbl">
            <thead><tr><th>ID Program</th><th>Kecamatan / Lokasi</th><th class="r">Target (Ha)</th><th class="r">Realisasi (Ha)</th><th class="r">Progres</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($lap['rows'] as $r)
                <tr data-q="{{ $r['q'] }}" data-kec="{{ $r['kecamatan'] }}" data-status="{{ $r['status'] }}">
                    <td class="id">{{ $r['kode'] }}</td>
                    <td>{{ $r['kecamatan'] }}</td>
                    <td class="r mono">{{ $r['target_fmt'] }}</td>
                    <td class="r mono">{{ $r['realisasi_fmt'] }}</td>
                    <td class="r mono">{{ $r['progres'] }}%</td>
                    <td><span class="badge b-{{ $r['status_class'] }}">{{ $r['status'] }}</span></td>
                </tr>
                @endforeach
                <tr class="empty-row" style="display:none"><td colspan="6" class="empty">Tidak ada data untuk filter ini.</td></tr>
            </tbody>
            <tfoot>
                <tr><td colspan="2">TOTAL PROGRAM DITAMPILKAN</td><td class="r mono">{{ $lap['total_target'] }} Ha</td><td class="r mono">{{ $lap['total_realisasi'] }} Ha</td><td class="r mono">{{ $lap['total_progres'] }}%</td><td></td></tr>
            </tfoot>
        </table>
        </div>

        <div class="rep-foot">
            <div class="note"><b>Catatan Verifikasi</b>Data diturunkan dari hasil monitoring lapangan dan pembaruan penanggung jawab program. Luasan menggunakan satuan hektare.</div>
            <div class="sign">{{ $lap['tanggal'] }}<br>Administrator LahanKritisTrack<div class="nm">Administrator Sistem</div></div>
        </div>
    </div>
</article>
@endsection
