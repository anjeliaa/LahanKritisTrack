<div class="modal-backdrop" id="modal-import">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-uploadcloud"/></svg></span><span data-modal-title>Impor Data GeoJSON</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('admin.lahan.import') }}" enctype="multipart/form-data" data-ajax>
        @csrf
        <label class="dropzone" for="geojson-file" style="cursor:pointer" data-dropzone>
            <span class="big"><svg class="ic"><use href="#i-uploadcloud"/></svg></span>
            <b>Tarik &amp; lepas file GeoJSON di sini</b>
            <small>atau klik untuk memilih file (.geojson, .json — maks. 10 MB)</small>
            <input id="geojson-file" name="geojson" type="file" accept=".geojson,.json,application/geo+json,application/json" class="sr-only" data-fileinfo="geojson-info" required>
        </label>
        <div class="file-row" id="geojson-info" style="display:none"><svg class="ic"><use href="#i-filetext"/></svg><div><b data-file-name>-</b><small data-file-size>-</small></div><svg class="ic"><use href="#i-checkc"/></svg></div>
        <div class="hint">Berkas berisi FeatureCollection (Polygon / MultiPolygon, koordinat WGS84). Atribut yang dibaca: <code>objectid</code>, <code>kecamatan</code>, <code>kritis</code>, <code>luas</code> (Ha), <code>bpdas</code>, <code>wilayah</code>. Petak dengan <code>objectid</code> yang sudah ada akan diperbarui; lainnya ditambahkan. Bila <code>luas</code> kosong, luas dihitung dari geometri.</div>
        <div class="modal-foot">
            <button type="button" class="btn btn-outline" data-close>Batal</button>
            <button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-upload"/></svg> Impor Data</button>
        </div>
    </form>
  </div>
</div>
