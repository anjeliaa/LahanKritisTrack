/* LahanKritisTrack — interaksi umum (tema, modal, toast, filter tabel) */
(function () {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const ICON_OK = '<svg class="ic"><use href="#i-checkc"/></svg>';

  /* ---------- tema (terang/gelap) ---------- */
  const saved = localStorage.getItem('lk-theme');
  if (saved) document.documentElement.dataset.theme = saved;
  function toggleTheme() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('lk-theme', next);
  }

  /* ---------- toast ---------- */
  window.toast = function (msg, err) {
    let root = $('#toast-root');
    if (!root) { root = document.createElement('div'); root.id = 'toast-root'; document.body.appendChild(root); }
    const t = document.createElement('div');
    t.className = 'toast' + (err ? ' err' : '');
    t.innerHTML = ICON_OK + '<span></span>';
    $('span', t).textContent = msg;
    root.appendChild(t);
    setTimeout(() => { t.style.transition = '.3s'; t.style.opacity = 0; setTimeout(() => t.remove(), 300); }, 2600);
  };

  /* ---------- modal ---------- */
  /* daftar petak asli per <select data-cascade>, agar bisa disaring ulang menurut kecamatan */
  function cascade(sel) {
    const target = sel.form.querySelector('[name="' + sel.dataset.cascade + '"]');
    if (!target) return;
    if (!target._all) target._all = Array.from(target.options).map((o) => ({ v: o.value, t: o.textContent, k: o.dataset.kec }));
    const keep = target.value;
    target.innerHTML = '';
    target._all.filter((o) => String(o.k) === String(sel.value)).forEach((o) => {
      const op = document.createElement('option'); op.value = o.v; op.textContent = o.t; op.dataset.kec = o.k; target.appendChild(op);
    });
    if (!target.options.length) { const op = document.createElement('option'); op.value = ''; op.textContent = 'Belum ada petak pada kecamatan ini'; target.appendChild(op); }
    if (Array.from(target.options).some((o) => o.value === keep)) target.value = keep;
  }
  function syncRanges(root) {
    $$('input[type=range][data-range]', root).forEach((r) => { const o = document.getElementById(r.dataset.range); if (o) o.textContent = r.value + '%'; });
  }
  function clearErrors(form) {
    $$('.err', form).forEach((n) => n.classList.remove('err'));
    $$('.field-err', form).forEach((n) => n.remove());
  }

  function openModal(id, trigger) {
    const bd = document.getElementById(id);
    if (!bd) return;
    const form = $('form', bd);
    const ds = (trigger && trigger.dataset) || {};
    if (form) {
      form.reset();
      clearErrors(form);
      if (ds.action) form.setAttribute('action', ds.action);
      let m = $('input[name="_method"]', form);
      if (ds.method && ds.method !== 'POST') {
        if (!m) { m = document.createElement('input'); m.type = 'hidden'; m.name = '_method'; form.appendChild(m); }
        m.value = ds.method;
      } else if (m) { m.remove(); }
      $$('select[data-cascade]', form).forEach(cascade);
      syncRanges(form);
    }
    if (ds.fill) {
      try {
        const data = JSON.parse(ds.fill);
        Object.keys(data).forEach((k) => {
          const f = bd.querySelector('[name="' + k + '"]');
          if (!f || f.type === 'file' || data[k] === null) return;
          f.value = data[k];
          f.dispatchEvent(new Event('input', { bubbles: true }));
          f.dispatchEvent(new Event('change', { bubbles: true }));
        });
      } catch (e) { /* abaikan */ }
    }
    if (ds.title) { const h = $('[data-modal-title]', bd); if (h) h.textContent = ds.title; }
    if (trigger) { const chip = $('.chip-ico', bd.querySelector('.modal-head') || bd); if (chip && ds.tone !== undefined) chip.className = 'chip-ico ' + (ds.tone || 'green'); }
    if (ds.detail) fillDetail(bd, JSON.parse(ds.detail));
    if (ds.name) $$('[data-fill-name]', bd).forEach((n) => (n.textContent = ds.name));
    const info = $('.file-row[id]', bd); if (info) info.style.display = 'none';
    bd.classList.add('open');
    document.body.style.overflow = 'hidden';
    const first = $('input:not([type=hidden]),select,textarea', bd);
    if (first && !ds.name) setTimeout(() => first.focus(), 50);
  }
  const KR = { 'Sangat Kritis': 'sangat', 'Kritis': 'kritis', 'Agak Kritis': 'agak', 'Potensial Kritis': 'potensial', 'Tidak Kritis': 'tidak' };
  function fillDetail(bd, d) {
    const set = (k, v) => { const n = bd.querySelector('[data-field="' + k + '"]'); if (n) n.textContent = v; };
    const all = (window.APP && window.APP.kegiatan) || [];
    const kg = all.filter((k) => (d.id_lahan ? Number(k.id_lahan) === Number(d.id_lahan) : k.kecamatan === d.kec));
    const esc = (t) => String(t).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    set('nama', d.nama); set('sub', 'KECAMATAN · ' + d.nama.toUpperCase() + (d.id_lahan ? ' · PETAK #' + d.id_lahan : '')); set('luas', d.luas); set('bpdas', d.bpdas); set('jml', kg.length + ' Program');
    const dsc = bd.querySelector('[data-field="desc"]'); if (dsc) dsc.innerHTML = 'Petak lahan seluas <b>' + esc(d.luas) + '</b> dengan tingkat kekritisan <b>' + esc(d.kritis.toLowerCase()) + '</b>.';
    const kr = bd.querySelector('[data-field="kritis"]'); if (kr) { kr.textContent = d.kritis; kr.className = 'badge b-' + KR[d.kritis]; }
    const list = bd.querySelector('[data-field="kegiatan"]');
    if (list) list.innerHTML = kg.length ? kg.map((k) => '<div class="mini-item"><span>' + esc(k.nama) + '</span><span class="badge b-' + k.status_class + '">' + k.status + '</span></div>').join('') : '<div class="empty">Belum ada kegiatan pada petak ini.</div>';
  }
  function closeModal(bd) {
    if (!bd) return;
    bd.classList.remove('open');
    if (!$('.modal-backdrop.open')) document.body.style.overflow = '';
  }
  window.openModal = openModal; window.closeModal = closeModal;

  document.addEventListener('click', function (e) {
    const t = e.target;
    if (t.closest('[data-theme-toggle]')) return toggleTheme();
    const op = t.closest('[data-open]');
    if (op) { e.preventDefault(); return openModal(op.dataset.open, op); }
    if (t.closest('[data-close]')) return closeModal(t.closest('.modal-backdrop'));
    if (t.classList.contains('modal-backdrop')) return closeModal(t);
    const ts = t.closest('[data-toast]');
    if (ts) { e.preventDefault(); return toast(ts.dataset.toast); }
    if (t.closest('[data-print]')) { e.preventDefault(); return window.print(); }
    const eye = t.closest('[data-eye]');
    if (eye) {
      const inp = document.getElementById(eye.dataset.eye);
      inp.type = inp.type === 'password' ? 'text' : 'password';
      const use = $('use', eye); use.setAttribute('href', inp.type === 'password' ? '#i-eye' : '#i-eyeoff');
    }
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal($('.modal-backdrop.open')); });

  /* form modal: kirim ke server via fetch; galat validasi ditampilkan tanpa menutup modal */
  document.addEventListener('submit', function (e) {
    const f = e.target.closest('form[data-ajax]');
    if (!f) return;
    e.preventDefault();
    if (f.dataset.busy) return;
    const btn = $('button[type=submit]', f);
    const token = (document.querySelector('meta[name="csrf-token"]') || {}).content;
    clearErrors(f);
    f.dataset.busy = '1'; if (btn) btn.disabled = true;
    fetch(f.getAttribute('action'), {
      method: 'POST',
      body: new FormData(f),
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token || '' }
    }).then(async (r) => {
      const j = await r.json().catch(() => ({}));
      if (r.ok) {
        closeModal(f.closest('.modal-backdrop'));
        toast(j.message || 'Berhasil disimpan');
        setTimeout(() => window.location.reload(), 500);   /* pesan sama ditampilkan lagi dari flash sesi */
        return;
      }
      if (r.status === 422 && j.errors) {
        let first = '';
        Object.keys(j.errors).forEach((k) => {
          const msg = j.errors[k][0]; first = first || msg;
          const fld = f.querySelector('[name="' + k + '"]');
          if (fld) {
            fld.classList.add('err');
            const box = fld.closest('.field'); if (box) { const p = document.createElement('div'); p.className = 'field-err'; p.textContent = msg; box.appendChild(p); }
          }
        });
        return toast(first, true);
      }
      if (r.status === 419) return toast('Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.', true);
      if (r.status === 401) { window.location.href = '/login'; return; }
      toast(j.message || 'Terjadi kesalahan pada server (' + r.status + ').', true);
    }).catch(() => toast('Tidak dapat terhubung ke server.', true))
      .finally(() => { delete f.dataset.busy; if (btn) btn.disabled = false; });
  });

  /* kecamatan -> daftar petak */
  document.addEventListener('change', function (e) { const c = e.target.closest('select[data-cascade]'); if (c) cascade(c); });

  /* info berkas terpilih + tarik & lepas */
  document.addEventListener('change', function (e) {
    const inp = e.target.closest('input[type=file][data-fileinfo]');
    if (!inp) return;
    const box = document.getElementById(inp.dataset.fileinfo); if (!box) return;
    const fl = inp.files && inp.files[0];
    box.style.display = fl ? '' : 'none';
    if (fl) { $('[data-file-name]', box).textContent = fl.name; $('[data-file-size]', box).textContent = (fl.size / 1024 < 1024 ? Math.round(fl.size / 1024) + ' KB' : (fl.size / 1048576).toFixed(1) + ' MB'); }
  });
  $$('[data-dropzone]').forEach(function (z) {
    ['dragenter', 'dragover'].forEach((ev) => z.addEventListener(ev, (e) => { e.preventDefault(); z.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach((ev) => z.addEventListener(ev, (e) => { e.preventDefault(); z.classList.remove('drag'); }));
    z.addEventListener('drop', (e) => { const inp = $('input[type=file]', z); if (inp && e.dataTransfer.files.length) { inp.files = e.dataTransfer.files; inp.dispatchEvent(new Event('change', { bubbles: true })); } });
  });

  /* range slider -> label */
  document.addEventListener('input', function (e) {
    const r = e.target.closest('input[type=range][data-range]');
    if (r) { const o = document.getElementById(r.dataset.range); if (o) o.textContent = r.value + '%'; }
  });

  /* ---------- filter tabel (cari + select) ---------- */
  $$('[data-table-filter]').forEach(function (box) {
    const table = document.getElementById(box.dataset.tableFilter);
    if (!table) return;
    const q = $('.tf-q', box), kec = $('.tf-kec', box), st = $('.tf-status', box);
    const rows = $$('tbody tr[data-q]', table);
    function run() {
      const qq = (q && q.value || '').toLowerCase().trim();
      let shown = 0;
      rows.forEach(function (r) {
        const ok = (!qq || r.dataset.q.includes(qq)) && (!kec || !kec.value || r.dataset.kec === kec.value) && (!st || !st.value || r.dataset.status === st.value);
        r.style.display = ok ? '' : 'none'; if (ok) shown++;
      });
      const em = $('tr.empty-row', table); if (em) em.style.display = shown ? 'none' : '';
    }
    [q, kec, st].forEach((el) => el && el.addEventListener('input', run));
  });
})();
