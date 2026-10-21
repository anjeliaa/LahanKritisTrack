{{-- Riwayat seluruh catatan monitoring pada satu kegiatan (diisi oleh app.js dari atribut data-riwayat) --}}
<div class="modal-backdrop" id="modal-riwayat">
  <div class="modal wide">
    <div class="modal-head">
        <h3><span class="chip-ico blue"><svg class="ic"><use href="#i-clipcheck"/></svg></span><span>Riwayat Monitoring</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <div class="modal-body">
        <h3 style="font-size:17px;font-weight:800" data-fill-name>-</h3>
        <div class="mono" style="font-size:11px;font-weight:700;color:var(--faint)" data-field="sub">-</div>

        <div class="rw-summary">
            <div><small>Progres Terbaru</small><div class="prog-cell"><div class="progress"><i data-field="bar" style="width:0"></i></div><b data-field="progres">0%</b></div></div>
            <div><small>Kondisi Terbaru</small><span class="badge" data-field="kondisi">-</span></div>
            <div><small>Jumlah Catatan</small><b class="mono" data-field="jumlah">0</b></div>
        </div>

        <div class="tl" data-field="riwayat"></div>

        <div class="modal-foot"><button type="button" class="btn btn-outline" data-close>Tutup</button></div>
    </div>
  </div>
</div>
