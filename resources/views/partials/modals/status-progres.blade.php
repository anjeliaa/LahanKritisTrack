<div class="modal-backdrop" id="modal-status-progres">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-refresh"/></svg></span><span data-modal-title>Update Status Progres Lapangan</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" data-demo="Progres lapangan diperbarui">
        <div class="field"><label>Pilih Kegiatan Monitoring</label><select class="select" name="kegiatan">@foreach($kegiatan as $k)
                    <option value="{{ $k['nama'] }}">{{ $k['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Capaian Progres Lapangan (%)</label><input class="input" name="progres" type="number" min="0" max="100" placeholder="mis. 75"></div>
        <div class="field"><label>Kondisi Perkembangan Lahan</label><select class="select" name="kondisi"><option>Membaik</option><option>Stabil</option><option>Memburuk</option></select></div>
        <div class="field"><label>Unggah Foto Dokumentasi Lapangan</label><input class="input" type="file" accept="image/*"></div>
        <div class="field"><label>Catatan Tambahan Petugas</label><textarea class="input" placeholder="Mis. Vegetasi tumbuh baik, erosi terisolasi..."></textarea></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
