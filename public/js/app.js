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
  function openModal(id, trigger) {
    const bd = document.getElementById(id);
    if (!bd) return;
    const form = $('form', bd);
    if (form && !(trigger && trigger.dataset.fill)) form.reset();
    if (trigger && trigger.dataset.fill) {
      try {
        const data = JSON.parse(trigger.dataset.fill);
        Object.keys(data).forEach((k) => {
          const f = bd.querySelector('[name="' + k + '"]');
          if (f) { f.value = data[k]; f.dispatchEvent(new Event('input')); }
        });
      } catch (e) { /* abaikan */ }
    }
    if (trigger && trigger.dataset.title) { const h = $('[data-modal-title]', bd); if (h) h.textContent = trigger.dataset.title; }
    if (trigger) { const chip = $('.chip-ico', bd.querySelector('.modal-head') || bd); if (chip && trigger.dataset.tone !== undefined) chip.className = 'chip-ico ' + (trigger.dataset.tone || 'green'); }
    if (trigger && trigger.dataset.detail) fillDetail(bd, JSON.parse(trigger.dataset.detail));
    if (trigger && trigger.dataset.name) $$('[data-fill-name]', bd).forEach((n) => (n.textContent = trigger.dataset.name));
    bd.classList.add('open');
    document.body.style.overflow = 'hidden';
    const first = $('input:not([type=hidden]),select,textarea', bd);
    if (first && !trigger.dataset.name) setTimeout(() => first.focus(), 50);
  }
  const KR = { 'Sangat Kritis': 'sangat', 'Kritis': 'kritis', 'Agak Kritis': 'agak', 'Potensial Kritis': 'potensial' };
  function fillDetail(bd, d) {
    const set = (k, v) => { const n = bd.querySelector('[data-field="' + k + '"]'); if (n) n.textContent = v; };
    const kg = ((window.APP && window.APP.kegiatan) || []).filter((k) => k.kecamatan === d.kec);
    set('nama', d.nama); set('sub', 'KECAMATAN · ' + d.nama.toUpperCase()); set('luas', d.luas); set('bpdas', d.bpdas); set('jml', kg.length + ' Program');
    const dsc = bd.querySelector('[data-field="desc"]'); if (dsc) dsc.innerHTML = 'Area lahan kritis seluas <b>' + d.luas + '</b> dengan tingkat kekritisan <b>' + d.kritis.toLowerCase() + '</b>.';
    const kr = bd.querySelector('[data-field="kritis"]'); if (kr) { kr.textContent = d.kritis; kr.className = 'badge b-' + KR[d.kritis]; }
    const list = bd.querySelector('[data-field="kegiatan"]');
    if (list) list.innerHTML = kg.length ? kg.map((k) => '<div class="mini-item"><span>' + k.nama + '</span><span class="badge b-' + k.status_class + '">' + k.status + '</span></div>').join('') : '<div class="empty">Belum ada kegiatan pada kecamatan ini.</div>';
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

  /* form di modal: simulasi simpan (frontend saja) */
  document.addEventListener('submit', function (e) {
    const f = e.target.closest('form[data-demo]');
    if (!f) return;
    e.preventDefault();
    closeModal(f.closest('.modal-backdrop'));
    toast(f.dataset.demo);
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
