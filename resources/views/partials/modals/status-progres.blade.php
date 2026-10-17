<div class="modal-backdrop" id="modal-status-progres">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-refresh"/></svg></span><span data-modal-title>Update Status Progres Lapangan</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('monitoring.store') }}" enctype="multipart/form-data" data-ajax>
        @csrf
        <div class="field"><label>Pilih Kegiatan Monitoring</label><select class="select" name="id_kegiatan" required>@foreach($kegiatan as $k)
                    <option value="{{ $k['id'] }}">{{ $k['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Capaian Progres Lapangan (%)</label><input class="input" name="progres_persen" type="number" min="0" max="100" step="1" placeholder="mis. 75" required></div>
        <div class="field"><label>Kondisi Perkembangan Lahan</label><select class="select" name="kondisi_lahan">@foreach($kondisi_opts as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach</select></div>
        <div class="field"><label>Unggah Foto Dokumentasi Lapangan</label><input class="input" name="foto_dokumentasi" type="file" accept="image/jpeg,image/png,image/webp"></div>
        <div class="field"><label>Catatan Tambahan Petugas</label><textarea class="input" name="catatan" placeholder="Mis. Vegetasi tumbuh baik, erosi terisolasi..." maxlength="2000"></textarea></div>
        <div class="hint" style="margin-bottom:8px">Pembaruan ini menambah satu catatan monitoring baru (tanggal hari ini) pada riwayat kegiatan.</div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
