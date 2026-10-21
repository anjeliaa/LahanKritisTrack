<div class="modal-backdrop" id="modal-status-kegiatan">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-refresh"/></svg></span><span data-modal-title>Update Status Kegiatan Rehabilitasi</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('kegiatan.status') }}" data-ajax>
        @csrf
        <div class="field"><label>Pilih Kegiatan Rehabilitasi</label><select class="select" name="kegiatan" required>@foreach($kegiatan_opsi as $k)
                    <option value="{{ $k['id'] }}">{{ $k['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Status Kegiatan Baru</label><select class="select" name="status"><option value="Berjalan">Berjalan</option><option value="Selesai">Selesai</option><option value="Rencana">Rencana</option></select></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
