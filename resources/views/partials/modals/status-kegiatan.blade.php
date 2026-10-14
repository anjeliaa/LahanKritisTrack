<div class="modal-backdrop" id="modal-status-kegiatan">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-refresh"/></svg></span><span data-modal-title>Update Status Kegiatan Rehabilitasi</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" data-demo="Status kegiatan diperbarui">
        <div class="field"><label>Pilih Kegiatan Rehabilitasi</label><select class="select" name="kegiatan">@foreach($kegiatan as $k)
                    <option value="{{ $k['nama'] }}">{{ $k['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Status Kegiatan Baru</label><select class="select" name="status"><option>Berjalan</option><option>Selesai</option><option>Rencana</option></select></div>
        <div class="field"><label>Catatan Tambahan Status</label><textarea class="input" placeholder="Catatan perubahan status kegiatan..."></textarea></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
