@extends('layouts.internal')
@section('title', 'Peta Lahan Kritis — LahanKritisTrack')

@section('content')
<div class="card map-card">
    @include('partials.map-filter', ['kritisFilter' => $kritis_ada])

    <div class="map-grid">
        <div class="map-box"><div id="map" data-mode="petugas" data-geojson="{{ route('lahan.geojson') }}"></div></div>
        <div class="info-col">
            <div class="info-card">
                <h3 id="kec-nama">Belum ada yang dipilih</h3>
                <div class="eyebrow" id="kec-sub">KECAMATAN</div>
                <p class="desc" id="kec-desc">Pilih polygon pada peta untuk melihat detail.</p>
                <div class="kv-list">
                    <div class="kv"><span>Tingkat kekritisan</span><span class="badge" id="kec-badge">-</span></div>
                    <div class="kv"><span>Luas Lahan Kritis</span><b class="mono" id="kec-luas">-</b></div>
                    <div class="kv"><span>Instansi Pembina</span><b id="kec-bpdas">-</b></div>
                    <div class="kv"><span>Kegiatan Terkait</span><b class="mono" id="kec-jml">-</b></div>
                </div>
            </div>
            <div class="info-card" style="padding:16px">
                <h3 style="font-size:12px" id="kec-kegiatan-title">Kegiatan Rehabilitasi</h3>
                <div class="mini-list" id="kec-kegiatan"></div>
            </div>
            <button type="button" id="btn-add-here" class="btn btn-primary btn-block" style="padding:12px" data-open="modal-kegiatan" data-title="Tambah Kegiatan Rehabilitasi Baru" data-tone="green" data-action="{{ route('kegiatan.store') }}" data-method="POST"><svg class="ic"><use href="#i-plus"/></svg> Tambah kegiatan di sini</button>
            @include('partials.map-result-list')
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
