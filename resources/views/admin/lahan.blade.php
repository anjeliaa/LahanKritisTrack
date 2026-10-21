@extends('layouts.internal')
@section('title', 'Data Lahan Kritis — LahanKritisTrack')

@section('content')
<div class="card" data-table-filter="tbl-lahan">
    <div class="card-head">
        <h2>Data Petak Lahan Kritis</h2>
        <div class="acts">
            <button type="button" class="btn btn-outline" data-open="modal-import"><svg class="ic"><use href="#i-upload"/></svg> Impor GeoJSON</button>
            <button type="button" class="btn btn-primary" data-open="modal-lahan-tambah"><svg class="ic"><use href="#i-plus"/></svg> Tambah Data</button>
        </div>
    </div>
    <div class="toolbar">
        <div class="search"><svg class="ic"><use href="#i-search"/></svg><input class="input tf-q" type="search" placeholder="Cari kecamatan / nomor petak..."></div>
        <select class="select tool-select tf-kec"><option value="">Semua Kecamatan</option>
            @foreach($kecamatan as $c)
            <option value="{{ $c['nama'] }}">{{ $c['nama'] }}@unless($c['ada_data']) (belum ada data)@endunless</option>
            @endforeach
        </select>
        <select class="select tool-select tf-status"><option value="">Semua Kekritisan</option>
            @foreach($kritis_data as $o)
            <option value="{{ $o['l'] }}">{{ $o['l'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="table-wrap">
        <table class="tbl" id="tbl-lahan">
            <thead><tr><th>Petak</th><th>Kecamatan</th><th>BPDAS</th><th>Tingkat Kekritisan</th><th>Luas</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($lahan as $k)
                <tr data-q="{{ $k['q'] }}" data-kec="{{ $k['kecamatan'] }}" data-status="{{ $k['kritis'] }}">
                    <td class="mono">#{{ $k['id'] }}</td>
                    <td class="name">{{ $k['kecamatan'] }}</td>
                    <td class="muted">BPDAS {{ $k['bpdas'] }}</td>
                    <td><span class="badge b-{{ $k['kritis_class'] }}">{{ $k['kritis'] }}</span>@if($k['rehab'])<small class="muted" style="display:block;margin-top:3px"><span class="badge b-pulih">Telah Direhabilitasi</span>@if($k['rehab_tanggal']) {{ $k['rehab_tanggal'] }}@endif</small>@endif</td>
                    <td class="num">{{ $k['luas_fmt'] }} Ha</td>
                    <td><div class="acts">
                        <button type="button" class="act act-detail" data-open="modal-detail" data-detail="{{ $k['detail'] }}"><svg class="ic"><use href="#i-eye"/></svg> Detail</button>
                        <button type="button" class="act act-ubah" data-open="modal-lahan" data-title="Ubah Data Petak #{{ $k['id'] }}" data-tone="amber" data-fill="{{ $k['fill'] }}" data-action="{{ $k['url'] }}" data-method="PUT"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                        <button type="button" class="act act-hapus" data-open="modal-hapus" data-name="Petak lahan #{{ $k['id'] }} ({{ $k['kecamatan'] }})" data-action="{{ $k['url_destroy'] }}" data-method="DELETE"><svg class="ic"><use href="#i-trash"/></svg> Hapus</button>
                    </div></td>
                </tr>
                @endforeach
                @if($lahan->isEmpty())
                <tr><td colspan="6" class="empty">Belum ada data lahan. Gunakan “Impor GeoJSON” untuk memuat data spasial.</td></tr>
                @endif
                <tr class="empty-row" style="display:none"><td colspan="6" class="empty">Tidak ada petak yang cocok.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
@include('partials.modals.detail-lahan')
@include('partials.modals.import')
@include('partials.modals.lahan-tambah')
@include('partials.modals.lahan')
@include('partials.modals.hapus')
@endsection

@push('scripts')
<script>window.APP = @json($app);</script>
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('js/lahan-form.js') }}"></script>
@endpush
