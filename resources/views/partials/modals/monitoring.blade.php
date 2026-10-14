<div class="modal-backdrop" id="modal-monitoring">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-camera"/></svg></span><span data-modal-title>Catat Laporan Monitoring Lapangan</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" data-demo="Laporan monitoring disimpan">
        <div class="field"><label>Kegiatan Terkait</label><select class="select" name="kegiatan">@foreach($kegiatan as $k)
                    <option value="{{ $k['nama'] }}">{{ $k['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Tanggal Monitoring</label><input class="input" name="tanggal" type="date"></div>
        <div class="field"><label>Progres Fisik Lapangan (%)</label><div class="range-row"><input type="range" name="progres" min="0" max="100" value="50" data-range="rv-mon"><span class="range-val" id="rv-mon">50%</span></div></div>
        <div class="field"><label>Kondisi Lahan</label><select class="select" name="kondisi"><option>Membaik</option><option>Stabil</option><option>Memburuk</option></select></div>
        <div class="field"><label>Unggah Foto Dokumentasi</label><input class="input" type="file" accept="image/*"></div>
        <div class="field"><label>Catatan Monitoring Lapangan</label><textarea class="input" name="catatan" placeholder="Catatan hasil monitoring..."></textarea></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
