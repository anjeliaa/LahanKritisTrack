<div class="modal-backdrop" id="modal-lahan-tambah">
  <div class="modal wide">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-plus"/></svg></span><span>Tambah Data Petak Lahan</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('admin.lahan.store') }}" data-ajax autocomplete="off">
        @csrf
        <div class="field"><label>Kecamatan</label>
            <select class="select" name="kecamatan_id" required>@foreach($kecamatan_semua as $c)<option value="{{ $c['id'] }}">{{ $c['nama'] }}</option>@endforeach</select></div>
        <div class="field"><label>Wilayah</label><input class="input" name="wilayah" placeholder="Kota Bengkulu" maxlength="255"></div>
        <div class="field"><label>Instansi Pembina (BPDAS)</label><input class="input" name="bpdas" value="{{ config('lahan.bpdas_default') }}" required maxlength="255"></div>
        <div class="field"><label>Tingkat Kekritisan</label>
            <select class="select" name="kritis">@foreach($kritis_opts as $o)<option value="{{ $o['l'] }}">{{ $o['l'] }}</option>@endforeach</select>
            <div class="hint">Sesuai data sumber (BPDAS). Sistem tidak menentukan tingkat kekritisan secara otomatis.</div></div>
        <div class="field"><label>Luas (Ha) <span class="muted" style="font-weight:500">— opsional</span></label>
            <input class="input" name="luas_ha" type="number" step="0.01" min="0" placeholder="Kosongkan untuk dihitung otomatis dari gambar">
        </div>

        <div class="field"><label>Area Petak</label>
            <div id="lahan-draw-map" class="draw-map" role="application" aria-label="Peta untuk menggambar area petak"></div>
            <div class="draw-bar">
                <span id="draw-info" aria-live="polite">Belum ada titik</span>
                <span class="draw-spacer"></span>
                <button type="button" class="btn btn-outline" id="draw-undo">Urungkan titik</button>
                <button type="button" class="btn btn-outline" id="draw-reset">Ulangi</button>
            </div>
            <div class="hint">Klik pada peta untuk menandai sudut-sudut petak secara berurutan (minimal 3 titik).</div>
            <details class="draw-paste">
                <summary>Atau tempel geometri GeoJSON</summary>
                <textarea class="input" id="draw-geometri" name="geometri" rows="4" spellcheck="false" placeholder='{"type":"Polygon","coordinates":[[[102.30,-3.80],[102.31,-3.80],[102.31,-3.81],[102.30,-3.80]]]}'></textarea>
                <div class="hint">Format Polygon atau MultiPolygon, koordinat [bujur, lintang] (WGS84).</div>
            </details>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn btn-outline" data-close>Batal</button>
            <button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button>
        </div>
    </form>
  </div>
</div>
