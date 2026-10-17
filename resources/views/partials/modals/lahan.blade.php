<div class="modal-backdrop" id="modal-lahan">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico amber"><svg class="ic"><use href="#i-pen"/></svg></span><span data-modal-title>Ubah Data Petak Lahan</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="#" data-ajax>
        @csrf
        <div class="field"><label>Kecamatan</label><select class="select" name="kecamatan_id" required>@foreach($kecamatan_semua as $c)<option value="{{ $c['id'] }}">{{ $c['nama'] }}</option>@endforeach</select></div>
        <div class="field"><label>Wilayah</label><input class="input" name="wilayah" placeholder="mis. Kota Bengkulu" maxlength="255"></div>
        <div class="field"><label>Instansi Pembina (BPDAS)</label><input class="input" name="bpdas" required maxlength="255"></div>
        <div class="field"><label>Tingkat Kekritisan</label><select class="select" name="kritis">@foreach($kritis_opts as $o)<option value="{{ $o['l'] }}">{{ $o['l'] }}</option>@endforeach</select></div>
        <div class="field"><label>Luas (Ha)</label><input class="input" name="luas_ha" type="number" step="0.01" min="0" required></div>
        <div class="hint">Bentuk polygon (geometri) hanya dapat diubah lewat Impor GeoJSON.</div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
