<div class="modal-backdrop" id="modal-kegiatan">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-sprout"/></svg></span><span data-modal-title>Tambah Kegiatan Rehabilitasi Baru</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('kegiatan.store') }}" data-ajax>
        @csrf
        <div class="field"><label>Nama Kegiatan</label><input class="input" name="nama_kegiatan" placeholder="mis. Penanaman Vegetasi DAS" required maxlength="255"></div>
        <div class="field"><label>Area Lahan Kritis (Kecamatan)</label>
            <select class="select" name="kecamatan_id" data-cascade="id_lahan" required>
                @foreach($kecamatan as $c)
                <option value="{{ $c['id'] }}">{{ $c['nama'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label>Petak Lahan</label>
            <select class="select" name="id_lahan" required>
                @foreach($petak as $p)
                <option value="{{ $p['id'] }}" data-kec="{{ $p['kecamatan_id'] }}">{{ $p['label'] }}</option>
                @endforeach
            </select>
            <div class="hint">Kegiatan menempel pada satu petak lahan (lihat nomor petak pada peta).</div>
        </div>
        <div class="field"><label>Jenis Rehabilitasi</label><input class="input" name="jenis_kegiatan" placeholder="mis. Reboisasi Hutan / Vegetasi" maxlength="255"></div>
        <div class="field"><label>Penanggung Jawab / Pelaksana</label><input class="input" name="penanggung_jawab" placeholder="Kelompok Tani / CV" maxlength="255"></div>
        <div class="field"><label>Target Luas (Ha)</label><input class="input" name="target_luas" type="number" step="0.01" min="0" placeholder="mis. 12.5" required></div>
        <div class="field"><label>Tanggal Mulai</label><input class="input" name="tanggal_mulai" type="date"></div>
        <div class="field"><label>Tanggal Selesai</label><input class="input" name="tanggal_selesai" type="date"></div>
        <div class="field"><label>Status</label><select class="select" name="status"><option value="Rencana">Rencana</option><option value="Berjalan">Berjalan</option><option value="Selesai">Selesai</option></select></div>
        <div class="field"><label>Publikasikan ke Tampilan Masyarakat?</label><select class="select" name="is_published"><option value="1">Ya, Publikasikan</option><option value="0">Tidak</option></select></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
