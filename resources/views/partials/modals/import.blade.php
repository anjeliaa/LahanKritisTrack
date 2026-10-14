<div class="modal-backdrop" id="modal-import">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-uploadcloud"/></svg></span><span data-modal-title>Impor Data GeoJSON</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" data-demo="Impor GeoJSON berhasil (simulasi frontend)">
        <label class="dropzone" for="geojson-file" style="cursor:pointer">
            <span class="big"><svg class="ic"><use href="#i-uploadcloud"/></svg></span>
            <b>Tarik &amp; lepas file GeoJSON di sini</b>
            <small>atau klik untuk memilih file (.geojson, .json — maks. 10 MB)</small>
            <input id="geojson-file" type="file" accept=".geojson,.json" class="sr-only">
        </label>
        <div class="file-row"><svg class="ic"><use href="#i-filetext"/></svg><div><b>kecamatan_bengkulu.geojson</b><small>248 KB · 9 fitur terdeteksi</small></div><svg class="ic"><use href="#i-checkc"/></svg></div>
        <div class="modal-foot">
            <button type="button" class="btn btn-outline" data-close>Batal</button>
            <button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-upload"/></svg> Impor Data</button>
        </div>
    </form>
  </div>
</div>
