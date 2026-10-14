<div class="modal-backdrop" id="modal-kegiatan">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-sprout"/></svg></span><span data-modal-title>Tambah Kegiatan Rehabilitasi Baru</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" data-demo="Kegiatan rehabilitasi disimpan">
        <div class="field"><label>Nama Kegiatan</label><input class="input" name="nama" placeholder="mis. Penanaman Vegetasi DAS" required></div>
        <div class="field"><label>Area Lahan Kritis (Kecamatan)</label><select class="select" name="kecamatan">@foreach($kecamatan as $c)
                    <option value="{{ $c['nama'] }}">{{ $c['nama'] }}</option>
                    @endforeach</select></div>
        <div class="field"><label>Jenis Rehabilitasi</label><input class="input" name="jenis" placeholder="mis. Reboisasi Hutan / Vegetasi"></div>
        <div class="field"><label>Penanggung Jawab / Pelaksana</label><input class="input" name="pj" placeholder="Kelompok Tani / CV"></div>
        <div class="field"><label>Target Luas (Ha)</label><input class="input" name="target" type="number" step="0.1" placeholder="mis. 12.5"></div>
        <div class="field"><label>Tanggal Mulai</label><input class="input" name="mulai" type="date"></div>
        <div class="field"><label>Tanggal Selesai</label><input class="input" name="selesai" type="date"></div>
        <div class="field"><label>Publikasikan ke Tampilan Masyarakat?</label><select class="select" name="publish"><option>Ya, Publikasikan</option><option>Tidak</option></select></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
