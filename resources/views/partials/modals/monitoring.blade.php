<div class="modal-backdrop" id="modal-monitoring">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-camera"/></svg></span><span data-modal-title>Catat Laporan Monitoring Lapangan</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('monitoring.store') }}" enctype="multipart/form-data" data-ajax>
        @csrf
        <div class="field"><label>Kegiatan Terkait</label><select class="select" name="id_kegiatan" required>@foreach($kegiatan_opsi as $k)
                    <option value="{{ $k['id'] }}">{{ $k['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Tanggal Monitoring</label><input class="input" name="tanggal_monitoring" type="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}"></div>
        <div class="field"><label>Progres Fisik Lapangan (%)</label><div class="range-row"><input type="range" name="progres_persen" min="0" max="100" value="50" data-range="rv-mon"><span class="range-val" id="rv-mon">50%</span></div></div>
        <div class="field"><label>Kondisi Lahan</label><select class="select" name="kondisi_lahan">@foreach($kondisi_opts as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach</select></div>
        <div class="field"><label>Unggah Foto Dokumentasi</label><input class="input" name="foto_dokumentasi" type="file" accept="image/jpeg,image/png,image/webp"><div class="hint">JPG / PNG / WEBP, maks. 4 MB. Saat mengubah laporan, kosongkan untuk mempertahankan foto yang ada.</div></div>
        <div class="field"><label>Catatan Monitoring Lapangan</label><textarea class="input" name="catatan" placeholder="Catatan hasil monitoring..." maxlength="2000"></textarea></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
