@extends('layouts.internal')

@section('title', 'Impor Data GeoJSON — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => 'uploadcloud',
    'path'  => 'Data Lahan Kritis',
    'title' => 'Impor Data GeoJSON',
    'desc'  => 'Muat data spasial petak lahan dari berkas GeoJSON.',
    'back'  => route('admin.lahan'),
])
@endsection

@section('content')
<form method="POST" action="{{ route('admin.lahan.import') }}" enctype="multipart/form-data" class="card form-card" id="form-import">
    @csrf

    @error('geojson')<div class="form-alert">{{ $message }}</div>@enderror

    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Berkas GeoJSON</h2>
            <p>Format .geojson atau .json, maksimal 10 MB.</p>
        </div>
        <div>
            <label class="dropzone" for="geojson-file" style="cursor:pointer" data-dropzone>
                <span class="big"><svg class="ic"><use href="#i-uploadcloud"/></svg></span>
                <b>Tarik &amp; lepas file GeoJSON di sini</b>
                <small>atau klik untuk memilih file (.geojson, .json — maks. 10 MB)</small>
                <input id="geojson-file" name="geojson" type="file" accept=".geojson,.json,application/geo+json,application/json" class="sr-only" data-fileinfo="geojson-info" required>
            </label>
            <div class="file-row" id="geojson-info" style="display:none;margin-top:12px">
                <svg class="ic"><use href="#i-filetext"/></svg>
                <div><b data-file-name>-</b><small data-file-size>-</small></div>
                <svg class="ic"><use href="#i-checkc"/></svg>
            </div>
        </div>
    </section>

    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Format yang Dibaca</h2>
            <p>Berkas berisi FeatureCollection dengan geometri Polygon atau MultiPolygon, koordinat WGS84.</p>
        </div>
        <div>
            <div class="dz-chips">
                <code>objectid</code><code>kecamatan</code><code>kritis</code><code>luas</code><code>bpdas</code><code>wilayah</code>
            </div>
            <div class="note-box" style="margin-top:14px">
                Petak dengan <code>objectid</code> yang sudah ada akan <b>diperbarui</b>; selebihnya <b>ditambahkan</b>.
                Bila <code>luas</code> (Ha) kosong, luas dihitung dari geometri.
            </div>
        </div>
    </section>

    <div class="form-foot">
        <a href="{{ route('admin.lahan') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-import"><svg class="ic" aria-hidden="true"><use href="#i-upload"/></svg> Impor Data</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('form-import').addEventListener('submit', function (e) {
    var b = document.getElementById('btn-import');
    setTimeout(function () { b.disabled = true; b.lastChild.textContent = ' Memproses...'; }, 0);
});
</script>
@endpush