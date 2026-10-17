@extends('layouts.internal')
@section('title', 'Laporan — LahanKritisTrack')

@section('content')
<form class="card no-print" method="GET" action="{{ route('admin.laporan') }}" id="form-laporan">
    <div class="card-head">
        <h2>Laporan Kegiatan dan Monitoring Terpadu</h2>
        <div class="acts">
            <button type="button" class="btn btn-pdf" data-print><svg class="ic"><use href="#i-filetext"/></svg> Unduh PDF</button>
            <a class="btn btn-excel" href="{{ $excel_url }}"><svg class="ic"><use href="#i-sheet"/></svg> Unduh Excel</a>
        </div>
    </div>
    <div class="toolbar">
        <select class="select tool-select" name="kecamatan_id" onchange="this.form.submit()"><option value="">Semua Kecamatan</option>
            @foreach($kecamatan as $c)
            <option value="{{ $c['id'] }}" @selected((string) $filter['kecamatan_id'] === (string) $c['id'])>{{ $c['nama'] }}@unless($c['ada_data']) (belum ada data)@endunless</option>
            @endforeach
        </select>
        <select class="select tool-select" name="periode" onchange="this.form.submit()"><option value="">Semua Periode</option>
            @foreach($periode_opts as $v => $l)
            <option value="{{ $v }}" @selected($filter['periode'] === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select class="select tool-select" name="status" onchange="this.form.submit()"><option value="">Semua Status</option>
            @foreach($status_opts as $s)
            <option value="{{ $s }}" @selected($filter['status'] === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
</form>

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
            <thead><tr><th>ID Program</th><th>Kegiatan</th><th>Kecamatan / Lokasi</th><th class="r">Target (Ha)</th><th class="r">Realisasi (Ha)</th><th class="r">Progres</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($lap['rows'] as $r)
                <tr>
                    <td class="id">{{ $r['kode'] }}</td>
                    <td>{{ $r['nama'] }}</td>
                    <td>{{ $r['kecamatan'] }}</td>
                    <td class="r mono">{{ $r['target_fmt'] }}</td>
                    <td class="r mono">{{ $r['realisasi_fmt'] }}</td>
                    <td class="r mono">{{ $r['progres'] }}%</td>
                    <td><span class="badge b-{{ $r['status_class'] }}">{{ $r['status'] }}</span></td>
                </tr>
                @endforeach
                @if($lap['rows']->isEmpty())
                <tr><td colspan="7" class="empty">Tidak ada data untuk filter ini.</td></tr>
                @endif
            </tbody>
            <tfoot>
                <tr><td colspan="3">TOTAL PROGRAM DITAMPILKAN</td><td class="r mono">{{ $lap['total_target'] }} Ha</td><td class="r mono">{{ $lap['total_realisasi'] }} Ha</td><td class="r mono">{{ $lap['total_progres'] }}%</td><td></td></tr>
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
