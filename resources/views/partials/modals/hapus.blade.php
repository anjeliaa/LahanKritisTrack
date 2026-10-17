<div class="modal-backdrop" id="modal-hapus">
  <div class="modal sm">
    <form class="dialog-body" method="POST" action="#" data-ajax>
        @csrf
        <div class="big chip-ico red" style="width:56px;height:56px;border-radius:50%;font-size:26px"><svg class="ic"><use href="#i-trash"/></svg></div>
        <h3>Hapus data ini?</h3>
        <p><b data-fill-name>Data</b> akan dihapus permanen dan tidak dapat dikembalikan.</p>
        <div class="modal-foot">
            <button type="button" class="btn btn-outline" data-close>Batal</button>
            <button type="submit" class="btn btn-danger"><svg class="ic"><use href="#i-trash"/></svg> Ya, Hapus</button>
        </div>
    </form>
  </div>
</div>
