@extends('layouts.internal')
@section('title', 'Peta Lahan Kritis — LahanKritisTrack')

@section('content')
<div class="card map-card">
    <div class="toolbar" style="padding:0 0 12px;border-bottom:1px solid var(--line2);justify-content:space-between">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <div class="search"><svg class="ic"><use href="#i-search"/></svg><input id="f-q" class="input" type="search" placeholder="Cari kecamatan..." autocomplete="off"></div>
            <select id="f-kritis" class="select tool-select"><option value="">Semua Kekritisan</option>
                @foreach($kritis_opts as $o)
                <option value="{{ $o['v'] }}">{{ $o['l'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="legend">
            <span><i style="background:#ef4444"></i>Sangat Kritis</span>
            <span><i style="background:#f97316"></i>Kritis</span>
            <span><i style="background:#eab308"></i>Agak Kritis</span>
            <span><i style="background:#10b981"></i>Potensial Kritis</span>
        </div>
    </div>
    <div class="map-grid">
        <div class="map-box"><div id="map" data-mode="petugas" data-geojson="{{ asset('data/kecamatan.geojson') }}"></div></div>
        <div class="info-col">
            <div class="info-card">
                <h3 id="kec-nama">Selebar</h3>
                <div class="eyebrow" id="kec-sub">KECAMATAN · SELEBAR</div>
                <p class="desc" id="kec-desc">Pilih polygon pada peta untuk melihat detail.</p>
                <div class="kv-list">
                    <div class="kv"><span>Tingkat kekritisan</span><span class="badge b-sangat" id="kec-badge">Sangat Kritis</span></div>
                    <div class="kv"><span>Luas Lahan Kritis</span><b class="mono" id="kec-luas">-</b></div>
                    <div class="kv"><span>Instansi Pembina</span><b id="kec-bpdas">-</b></div>
                    <div class="kv"><span>Kegiatan Terkait</span><b class="mono" id="kec-jml">-</b></div>
                </div>
            </div>
            <div class="info-card" style="padding:16px">
                <h3 style="font-size:12px">Kegiatan Rehabilitasi di Kecamatan Ini</h3>
                <div class="mini-list" id="kec-kegiatan"></div>
            </div>
            <button type="button" id="btn-add-here" class="btn btn-primary btn-block" style="padding:12px" data-open="modal-kegiatan" data-title="Tambah Kegiatan Rehabilitasi Baru" data-tone="green" data-fill='{"kecamatan":"Selebar"}'><svg class="ic"><use href="#i-plus"/></svg> Tambah kegiatan di sini</button>
        </div>
    </div>
</div>
@endsection

@section('modals')
@include('partials.modals.kegiatan')
@endsection

@push('scripts')
<script>window.APP = @json($app);</script>
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('js/map.js') }}"></script>
@endpush
