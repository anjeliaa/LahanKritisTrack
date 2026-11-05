@extends('layouts.internal')

@section('title', 'Detail Petak #' . $row['id'] . ' — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => 'pin',
    'path'  => 'Data Lahan Kritis',
    'title' => 'Detail Petak #' . $row['id'],
    'desc'  => $row['kecamatan'] . ' · BPDAS ' . $row['bpdas'],
    'back'  => route('admin.lahan'),
])
@endsection

@section('content')

{{-- Informasi petak --}}
<div class="card">
    <div class="card-head">
        <h2>Informasi Petak</h2>
        <a href="{{ route('admin.lahan.edit', $row['id']) }}" class="act act-ubah"><svg class="ic"><use href="#i-pen"/></svg> Ubah</a>
    </div>
    <dl class="kv-grid">
        <div>
            <dt>Tingkat Kekritisan</dt>
            <dd>
                <span class="badge b-{{ $row['kritis_class'] }}">{{ $row['kritis'] }}</span>
                @if($row['rehab'])
                <span class="badge b-pulih" style="margin-left:6px">Telah Direhabilitasi</span>
                @if($row['rehab_tanggal'])<small class="muted" style="display:block;margin-top:4px;font-weight:500">{{ $row['rehab_tanggal'] }}</small>@endif
                @endif
            </dd>
        </div>
        <div><dt>Luas Petak</dt><dd class="mono">{{ $row['luas_fmt'] }} Ha</dd></div>
        <div><dt>Kegiatan Terkait</dt><dd class="mono">{{ $kegiatan_lahan->count() }}</dd></div>
        <div><dt>Kecamatan</dt><dd>{{ $row['kecamatan'] }}</dd></div>
        <div><dt>Instansi Pembina</dt><dd>BPDAS {{ $row['bpdas'] }}</dd></div>
        <div><dt>Wilayah</dt><dd>{{ $item->wilayah ?: '—' }}</dd></div>
    </dl>
</div>

{{-- Kegiatan pada petak ini --}}
<div class="card">
    <div class="card-head">
        <h2>Kegiatan Rehabilitasi pada Petak Ini</h2>
    </div>
    <div class="table-wrap">
        <table class="tbl">
            <thead><tr><th>Kegiatan</th><th>Penanggung Jawab</th><th>Mulai</th><th>Target Selesai</th><th>Progres</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($kegiatan_lahan as $k)
                <tr>
                    <td class="name">{{ $k['nama'] }}</td>
                    <td class="muted">{{ $k['pj'] }}</td>
                    <td class="date">{{ $k['mulai'] }}</td>
                    <td class="date-end">{{ $k['selesai'] }}</td>
                    <td><div class="prog-cell"><div class="progress"><i style="width:{{ $k['progres'] }}%"></i></div><span>{{ $k['progres'] }}%</span></div></td>
                    <td><span class="badge b-{{ $k['status_class'] }}">{{ $k['status'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty">Belum ada kegiatan rehabilitasi pada petak ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection