@extends('layouts.internal')

@php
    $edit  = $item !== null;
    $judul = $edit ? 'Ubah Data Petak #' . $item->id_lahan : 'Tambah Data Petak Lahan';

    $kecPilih    = old('kecamatan_id', $item->kecamatan_id ?? collect($kecamatan_semua)->first()['id'] ?? null);
    $kritisPilih = old('kritis', $item->kritis ?? collect($kritis_opts)->first()['l'] ?? '');
    $bpdasPilih  = old('bpdas', $item->bpdas ?? config('lahan.bpdas_default'));
    $wilayahPilih = old('wilayah', $item->wilayah ?? '');
    $luasPilih   = old('luas_ha', $item->luas_ha ?? '');
@endphp

@section('title', $judul . ' — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => $edit ? 'pen' : 'plus',
    'path'  => 'Data Lahan Kritis',
    'title' => $judul,
    'desc'  => $edit ? 'Perbarui atribut petak, lalu simpan perubahan.' : 'Tandai area petak pada peta, lalu lengkapi atributnya.',
    'back'  => route('admin.lahan'),
])
@endsection

@section('content')
<form method="POST" action="{{ $edit ? route('admin.lahan.update', $item->id_lahan) : route('admin.lahan.store') }}" class="card form-card" id="form-lahan" autocomplete="off">
    @csrf
    @if($edit) @method('PUT') @endif

    @if($errors->any())
    <div class="form-alert">Ada {{ $errors->count() }} isian yang perlu diperbaiki. Periksa kolom yang bertanda merah.</div>
    @endif

    {{-- 1. Lokasi & instansi --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Lokasi &amp; Instansi</h2>
            <p>Kecamatan tempat petak berada dan instansi yang membina.</p>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="kecamatan_id">Kecamatan</label>
                <select id="kecamatan_id" class="select @error('kecamatan_id') err @enderror" name="kecamatan_id" required>
                    @foreach($kecamatan_semua as $c)
                    <option value="{{ $c['id'] }}" @selected((string) $kecPilih === (string) $c['id'])>{{ $c['nama'] }}</option>
                    @endforeach
                </select>
                @error('kecamatan_id')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="wilayah">Wilayah</label>
                <input id="wilayah" class="input @error('wilayah') err @enderror" name="wilayah" value="{{ $wilayahPilih }}" placeholder="Kota Bengkulu" maxlength="255">
                @error('wilayah')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field span2">
                <label for="bpdas">Instansi Pembina (BPDAS)</label>
                <input id="bpdas" class="input @error('bpdas') err @enderror" name="bpdas" value="{{ $bpdasPilih }}" maxlength="255" required>
                @error('bpdas')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 2. Kekritisan & luas --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Kekritisan &amp; Luas</h2>
            <p>Sesuai data sumber (BPDAS). Sistem tidak menentukan tingkat kekritisan secara otomatis.</p>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="kritis">Tingkat Kekritisan</label>
                <select id="kritis" class="select @error('kritis') err @enderror" name="kritis">
                    @foreach($kritis_opts as $o)
                    <option value="{{ $o['l'] }}" @selected($kritisPilih === $o['l'])>{{ $o['l'] }}</option>
                    @endforeach
                </select>
                @error('kritis')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="luas_ha">Luas (Ha) @unless($edit)<span class="muted" style="font-weight:500">— opsional</span>@endunless</label>
                <input id="luas_ha" class="input @error('luas_ha') err @enderror" name="luas_ha" type="number" step="0.01" min="0" value="{{ $luasPilih }}" placeholder="{{ $edit ? '' : 'Kosongkan untuk dihitung otomatis' }}" @if($edit) required @endif>
                @unless($edit)<div class="hint">Bila kosong, luas dihitung dari gambar area petak.</div>@endunless
                @error('luas_ha')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 3. Area / bentuk petak --}}
    @if($edit)
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Bentuk Petak</h2>
            <p>Polygon petak tidak diubah dari halaman ini.</p>
        </div>
        <div class="note-box">
            Bentuk polygon (geometri) hanya dapat diubah lewat <a href="{{ route('admin.lahan.impor') }}" class="link">Impor GeoJSON</a>.
            Petak dengan <code>objectid</code> dan kecamatan yang sama akan diperbarui.
        </div>
    </section>
    @else
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Area Petak</h2>
            <p>Klik pada peta untuk menandai sudut-sudut petak secara berurutan (minimal 3 titik).</p>
        </div>
        <div>
            <div id="lahan-draw-map" class="draw-map" role="application" aria-label="Peta untuk menggambar area petak"></div>
            <div class="draw-bar">
                <span id="draw-info" aria-live="polite">Belum ada titik</span>
                <span class="draw-spacer"></span>
                <button type="button" class="btn btn-outline" id="draw-undo">Urungkan titik</button>
                <button type="button" class="btn btn-outline" id="draw-reset">Ulangi</button>
            </div>
            @error('geometri')<div class="field-err" style="margin-top:8px">{{ $message }}</div>@enderror

            <details class="draw-paste" @if($errors->has('geometri') || old('geometri')) open @endif>
                <summary>Atau tempel geometri GeoJSON</summary>
                <textarea class="input" id="draw-geometri" name="geometri" rows="4" spellcheck="false" placeholder='{"type":"Polygon","coordinates":[[[102.30,-3.80],[102.31,-3.80],[102.31,-3.81],[102.30,-3.80]]]}'>{{ old('geometri') }}</textarea>
                <div class="hint">Format Polygon atau MultiPolygon, koordinat [bujur, lintang] (WGS84).</div>
            </details>
        </div>
    </section>
    @endif

    <div class="form-foot">
        <a href="{{ route('admin.lahan') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary"><svg class="ic" aria-hidden="true"><use href="#i-save"/></svg> {{ $edit ? 'Simpan Perubahan' : 'Simpan Petak' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('form-lahan').addEventListener('submit', function (e) {
    var b = e.target.querySelector('button[type=submit]');
    if (b) setTimeout(function () { b.disabled = true; }, 0);
});
</script>
@endpush

@unless($edit)
@push('scripts')
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script>
/* Gambar polygon dengan klik pada peta (Leaflet). Mengisi <textarea name="geometri"> dengan GeoJSON Polygon;
   server menghitung luas bila kolom Luas kosong. */
(function () {
    if (!window.L) return;
    var $ = function (id) { return document.getElementById(id); };
    var el = $('lahan-draw-map'), ta = $('draw-geometri'), info = $('draw-info');
    var map, poly, dots, pts = [];

    /* luas poligon bola (Ha), rumus yang sama dengan penghitungan di server */
    function areaHa(ring) {
        var R = 6378137, rad = function (d) { return d * Math.PI / 180; }, s = 0;
        for (var i = 0; i < ring.length - 1; i++) s += rad(ring[i + 1][0] - ring[i][0]) * (2 + Math.sin(rad(ring[i][1])) + Math.sin(rad(ring[i + 1][1])));
        return Math.abs(s * R * R / 2) / 10000;
    }

    function render() {
        poly.setLatLngs(pts);
        dots.clearLayers();
        pts.forEach(function (p) {
            L.circleMarker(p, { radius: 4, color: '#15803d', weight: 2, fillColor: '#fff', fillOpacity: 1, interactive: false }).addTo(dots);
        });
        if (pts.length >= 3) {
            var ring = pts.map(function (p) { return [+p.lng.toFixed(6), +p.lat.toFixed(6)]; });
            ring.push(ring[0]);
            ta.value = JSON.stringify({ type: 'Polygon', coordinates: [ring] });
            info.textContent = pts.length + ' titik · ≈ ' + areaHa(ring).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Ha';
        } else {
            ta.value = '';
            info.textContent = pts.length ? pts.length + ' titik (minimal 3)' : 'Belum ada titik';
        }
    }

    /* tempel GeoJSON -> tampilkan bentuknya pada peta */
    function dariTeks() {
        var g;
        try { g = JSON.parse(ta.value); } catch (e) { return; }
        if (g && g.type === 'Feature') g = g.geometry;
        if (g && g.type === 'FeatureCollection') g = g.features && g.features[0] && g.features[0].geometry;
        if (!g || !g.coordinates) return;
        var ring = g.type === 'Polygon' ? g.coordinates[0] : (g.type === 'MultiPolygon' ? g.coordinates[0][0] : null);
        if (!ring) return;
        var tutup = ring[0].join() === ring[ring.length - 1].join();
        pts = (tutup ? ring.slice(0, -1) : ring).map(function (c) { return L.latLng(c[1], c[0]); });
        var keep = ta.value;
        render();
        ta.value = keep;
        if (pts.length) map.fitBounds(poly.getBounds(), { padding: [20, 20] });
    }

    map = L.map(el, { zoomControl: true, scrollWheelZoom: true }).setView([-3.82, 102.3], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    poly = L.polygon([], { color: '#16a34a', weight: 2, fillColor: '#16a34a', fillOpacity: 0.25 }).addTo(map);
    dots = L.layerGroup().addTo(map);
    map.on('click', function (e) { pts.push(e.latlng); render(); });

    ta.addEventListener('change', dariTeks);
    $('draw-undo').addEventListener('click', function () { pts.pop(); render(); });
    $('draw-reset').addEventListener('click', function () { pts = []; render(); });

    setTimeout(function () { map.invalidateSize(); }, 80);

    /* isian lama (setelah validasi gagal) digambar ulang */
    if (ta.value.trim()) dariTeks();
})();
</script>
@endpush
@endunless