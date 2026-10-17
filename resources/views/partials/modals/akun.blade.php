<div class="modal-backdrop" id="modal-akun">
  <div class="modal">
    <div class="modal-head">
        <h3><span class="chip-ico green"><svg class="ic"><use href="#i-userplus"/></svg></span><span data-modal-title>Tambah Akun Pengguna System</span></h3>
        <button type="button" class="x-btn" data-close aria-label="Tutup"><svg class="ic"><use href="#i-x"/></svg></button>
    </div>
    <form class="modal-body" method="POST" action="{{ route('admin.akun.store') }}" data-ajax autocomplete="off">
        @csrf
        <div class="field"><label>Nama Lengkap</label><input class="input" name="nama" placeholder="mis. Budi Santoso" required maxlength="255"></div>
        <div class="field"><label>Username</label><input class="input" name="username" placeholder="budi.petugas" required maxlength="100"><div class="hint">Huruf, angka, titik, garis bawah, dan strip.</div></div>
        <div class="field"><label>Role Akses</label><select class="select" name="role"><option value="admin">Admin</option><option value="petugas" selected>Petugas</option></select></div>
        <div class="field"><label>Kata sandi</label><input class="input" name="password" type="password" minlength="8" placeholder="Minimal 8 karakter" autocomplete="new-password"><div class="hint">Wajib saat membuat akun. Saat mengubah, kosongkan jika tidak ingin mengganti.</div></div>
        <div class="modal-foot"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Data</button></div>
    </form>
  </div>
</div>
