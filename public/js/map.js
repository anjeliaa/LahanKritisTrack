/* LahanKritisTrack — peta interaktif (Leaflet) untuk halaman publik & petugas.
   Setiap polygon = satu petak lahan, diwarnai menurut kelas kekritisannya. */
(function () {
  const el = document.getElementById('map');
  if (!el || !window.L) return;
  const APP = window.APP || { kegiatan: [], kecamatan: [] };

  /* warna kelas kekritisan (samakan dengan variabel --tidak dst. di app.css) */
  const COL = Object.assign({ tidak: '#22c55e', potensial: '#eab308', agak: '#f97316', kritis: '#ef4444', sangat: '#b91c1c' }, (window.APP && window.APP.warna) || {});
  const LABEL = { tidak: 'Tidak Kritis', potensial: 'Potensial Kritis', agak: 'Agak Kritis', kritis: 'Kritis', sangat: 'Sangat Kritis' };
  const ORDER = ['tidak', 'potensial', 'agak', 'kritis', 'sangat'];

  const $ = (id) => document.getElementById(id);
  const norm = (s) => (s || '').toLowerCase();
  const nk = (s) => norm(s).replace('singaran', 'singgaran'); /* ejaan "Singaran/Singgaran Pati" disamakan */
  const fmt = (x) => Number(x).toLocaleString('id-ID', { maximumFractionDigits: 2 });
  const FILTER_IDS = ['f-q', 'f-kec', 'f-kritis', 'f-status'];

  const map = L.map(el, { zoomControl: true, scrollWheelZoom: false }).setView([-3.82, 102.3], 12);
  const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
  const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom: 17, attribution: '&copy; OpenTopoMap (CC-BY-SA)' });
  L.control.layers({ 'Peta jalan (OSM)': osm, 'Topografi': topo }, null, { position: 'topright' }).addTo(map);
  L.control.scale({ imperial: false }).addTo(map);
  el.addEventListener('click', () => map.scrollWheelZoom.enable());
  el.addEventListener('mouseleave', () => map.scrollWheelZoom.disable());

  const layers = {};
  let selLayer = null;  /* petak yang diklik */
  let selKec = null;    /* kecamatan yang dipilih dari daftar hasil */
  let allBounds = null;

  /* polygon: terisi warna kelas + garis tepi tipis gelap; yang tidak cocok filter dibuat pudar */
  const FILL_ON = 0.8, FILL_OFF = 0.1, FILL_HOVER = 0.92;

  function kecName(slug, fallback) {
    const k = (APP.kecamatan || []).find((x) => x.slug === slug);
    return k ? k.nama : fallback;
  }
  function kecId(slug) { const k = (APP.kecamatan || []).find((x) => x.slug === slug); return k ? k.id : ''; }
  function kegOf(nama) { return (APP.kegiatan || []).filter((k) => nk(k.kecamatan) === nk(nama)); }
  function baseStyle(f, on) {
    const c = COL[f.properties.kritis_class] || '#94a3b8';
    return on
      ? { color: '#0f172a', weight: 1, dashArray: null, fillColor: c, fillOpacity: FILL_ON }
      : { color: '#cbd5e1', weight: 0.8, dashArray: null, fillColor: c, fillOpacity: FILL_OFF };
  }
  function hasFilter() {
    return FILTER_IDS.some((id) => { const i = $(id); return i && i.value.trim() !== ''; });
  }
  function restyle() {
    Object.values(layers).forEach((l) => {
      const s = baseStyle(l.feature, l.feature._ok !== false);
      if (selKec && l.feature.properties.kec_slug === selKec && l.feature._ok !== false) s.weight = 2;
      l.setStyle(s);
    });
    if (selLayer) { selLayer.setStyle({ color: '#0f172a', weight: 3, fillOpacity: FILL_HOVER }); selLayer.bringToFront(); }
  }

  /* ---------- panel detail ---------- */
  function setPanel(o) {
    $('kec-nama').textContent = o.nama;
    $('kec-sub').textContent = o.sub;
    $('kec-desc').innerHTML = o.desc;
    const b = $('kec-badge'); b.textContent = o.badge; b.className = 'badge' + (o.cls ? ' b-' + o.cls : '');
    $('kec-luas').textContent = o.luas;
    $('kec-bpdas').textContent = o.bpdas;
    const kg = kegOf(o.kecNama);
    $('kec-jml').textContent = kg.length + ' Kegiatan';
    $('kec-kegiatan').innerHTML = kg.length ? kg.map((k) => '<div class="mini-item"><span>' + k.nama + '</span><span class="badge b-' + k.status_class + '">' + k.status + '</span></div>').join('') : '<div class="empty">Belum ada kegiatan rehabilitasi pada kecamatan ini.</div>';
    const add = $('btn-add-here'); if (add) add.dataset.fill = JSON.stringify(o.fill || {});
    document.querySelectorAll('.result-item').forEach((r) => r.classList.toggle('on', !!o.slug && r.dataset.slug === o.slug));
  }

  function selectLayer(lyr) {
    selLayer = lyr; selKec = null;
    const p = lyr.feature.properties, kn = kecName(p.kec_slug, p.kecamatan);
    restyle();
    setPanel({
      nama: kn, kecNama: kn,
      sub: 'KECAMATAN · ' + kn.toUpperCase() + ' · PETAK #' + p.id_lahan,
      desc: 'Petak lahan seluas <b>' + fmt(p.luas) + ' Ha</b> dengan tingkat kekritisan <b>' + p.kritis.toLowerCase() + '</b>.',
      badge: p.kritis, cls: p.kritis_class, luas: fmt(p.luas) + ' Ha', bpdas: 'BPDAS ' + p.bpdas,
      fill: { kecamatan_id: kecId(p.kec_slug), id_lahan: p.id_lahan }
    });
  }

  function showKec(slug) {
    selLayer = null; selKec = slug;
    restyle();
    const all = Object.values(layers).filter((l) => l.feature.properties.kec_slug === slug);
    if (!all.length) return;
    const p0 = all[0].feature.properties, kn = kecName(slug, p0.kecamatan);
    const by = {}; let ha = 0;
    all.forEach((l) => { const p = l.feature.properties; by[p.kritis_class] = (by[p.kritis_class] || 0) + p.luas; ha += p.luas; });
    const rinci = ORDER.filter((c) => by[c]).map((c) => LABEL[c].toLowerCase() + ' ' + fmt(by[c]) + ' Ha').join(', ');
    setPanel({
      nama: kn, kecNama: kn, slug: slug,
      sub: 'KECAMATAN · ' + kn.toUpperCase(),
      desc: 'Terdapat <b>' + all.length + ' petak</b> lahan seluas <b>' + fmt(ha) + ' Ha</b>: ' + rinci + '.',
      badge: '-', cls: '', luas: fmt(ha) + ' Ha', bpdas: 'BPDAS ' + p0.bpdas,
      fill: { kecamatan_id: kecId(slug) }
    });
  }

  /* kembalikan panel detail dan pilihan di peta ke kondisi awal (dipakai saat filter berubah atau Reset) */
  function clearSelection() {
    selLayer = null; selKec = null;
    $('kec-nama').textContent = 'Belum ada yang dipilih';
    $('kec-sub').textContent = 'KECAMATAN';
    $('kec-desc').textContent = 'Pilih polygon pada peta untuk melihat detail.';
    const b = $('kec-badge'); b.textContent = '-'; b.className = 'badge';
    $('kec-luas').textContent = '-';
    $('kec-bpdas').textContent = '-';
    $('kec-jml').textContent = '-';
    $('kec-kegiatan').innerHTML = '';
    const add = $('btn-add-here'); if (add) delete add.dataset.fill;
    document.querySelectorAll('.result-item').forEach((r) => r.classList.remove('on'));
  }

  /* ---------- filter & pencarian ---------- */
  function matches(p) {
    const q = norm($('f-q') && $('f-q').value).trim(), kec = $('f-kec') && $('f-kec').value, kr = $('f-kritis') && $('f-kritis').value, st = $('f-status') && $('f-status').value;
    const kn = kecName(p.kec_slug, p.kecamatan), kg = kegOf(kn);
    const okQ = !q || norm(kn).includes(q) || norm(p.kritis).includes(q) || kg.some((k) => norm(k.nama + ' ' + k.jenis + ' ' + k.pj).includes(q));
    return okQ && (!kec || kec === p.kec_slug) && (!kr || kr === p.kritis_class) && (!st || kg.some((k) => k.status === st));
  }

  function apply(fit) {
    const ok = [], groups = {};
    Object.values(layers).forEach((l) => {
      const p = l.feature.properties, m = matches(p);
      l.feature._ok = m;
      if (m) {
        ok.push(l);
        const g = groups[p.kec_slug] || (groups[p.kec_slug] = { nama: kecName(p.kec_slug, p.kecamatan), n: 0, ha: 0 });
        g.n++; g.ha += p.luas;
      }
    });
    restyle();
    const active = hasFilter();
    const rc = $('result-count'); if (rc) rc.innerHTML = active ? '<b>' + ok.length + '</b> dari ' + Object.keys(layers).length + ' petak lahan ditemukan' : '';
    const rl = $('result-list');
    if (rl) {
      /* default: daftar hasil kosong sampai pengguna mencari atau memilih filter */
      const slugs = Object.keys(groups).sort((a, b) => groups[a].nama.localeCompare(groups[b].nama));
      rl.innerHTML = !active ? '' : slugs.length
        ? slugs.map((s) => '<button type="button" class="result-item' + (selKec === s ? ' on' : '') + '" data-slug="' + s + '"><span><b>' + groups[s].nama + '</b> <small>' + groups[s].n + ' petak · ' + fmt(groups[s].ha) + ' Ha</small></span></button>').join('')
        : '<div class="empty">Tidak ada lahan yang cocok. Ubah kata kunci atau filter.</div>';
    }
    const st = $('f-status') && $('f-status').value, q = norm($('f-q') && $('f-q').value).trim();
    let cs = 0;
    document.querySelectorAll('.k-card').forEach((c) => {
      const show = !!groups[c.dataset.kec] && (!st || c.dataset.status === st) && (!q || c.dataset.q.includes(q) || norm(c.dataset.kecname).includes(q));
      c.style.display = show ? '' : 'none'; if (show) cs++;
    });
    const ke = $('k-empty'); if (ke) ke.style.display = cs ? 'none' : '';
    if (fit && !active && allBounds) map.fitBounds(allBounds.pad(0.05));
    else if (fit && ok.length) map.fitBounds(L.featureGroup(ok).getBounds().pad(0.2), { maxZoom: 14 });
  }

  /* ---------- muat polygon ---------- */
  fetch(el.dataset.geojson).then((r) => r.json()).then(function (gj) {
    const present = {};
    L.geoJSON(gj, {
      smoothFactor: 1,
      style: (f) => baseStyle(f, true),
      onEachFeature: function (f, lyr) {
        layers[f.properties.id_lahan] = lyr;
        present[f.properties.kritis_class] = true;
        lyr.bindTooltip(kecName(f.properties.kec_slug, f.properties.kecamatan) + ' · ' + f.properties.kritis + ' · ' + fmt(f.properties.luas) + ' Ha', { sticky: true, className: 'kec-label' });
        lyr.on({
          click: () => selectLayer(lyr),
          mouseover: () => { if (selLayer !== lyr && f._ok !== false) lyr.setStyle({ fillOpacity: FILL_HOVER }); },
          mouseout: () => { if (selLayer !== lyr) lyr.setStyle({ fillOpacity: f._ok === false ? FILL_OFF : FILL_ON }); }
        });
      }
    }).addTo(map);
    allBounds = L.featureGroup(Object.values(layers)).getBounds();
    map.fitBounds(allBounds.pad(0.05));

    /* legenda (pojok kanan bawah): hanya kelas yang ada di data */
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
      const d = L.DomUtil.create('div', 'map-legend-ctl');
      d.innerHTML = ORDER.filter((c) => present[c]).map((c) => '<div><i style="background:' + COL[c] + '"></i>' + LABEL[c] + '</div>').join('');
      L.DomEvent.disableClickPropagation(d);
      return d;
    };
    legend.addTo(map);

    /* kosongkan filter (browser kadang mengembalikan isian lama) dan jangan pilih apa pun otomatis */
    FILTER_IDS.forEach((id) => { const i = $(id); if (i) i.value = ''; });
    apply(false);
  });

  FILTER_IDS.forEach((id) => { const i = $(id); if (i) i.addEventListener('input', () => { clearSelection(); apply(true); }); });
  const rs = $('f-reset'); if (rs) rs.addEventListener('click', () => { FILTER_IDS.forEach((id) => { const i = $(id); if (i) i.value = ''; }); clearSelection(); apply(true); });
  document.addEventListener('click', (e) => {
    const r = e.target.closest('.result-item'); if (!r) return;
    showKec(r.dataset.slug);
    const ls = Object.values(layers).filter((l) => l.feature.properties.kec_slug === r.dataset.slug && l.feature._ok !== false);
    if (ls.length) map.fitBounds(L.featureGroup(ls).getBounds().pad(0.3), { maxZoom: 14 });
  });
  const sc = document.querySelectorAll('[data-scroll-map]'); sc.forEach((a) => a.addEventListener('click', () => setTimeout(() => map.invalidateSize(), 400)));
  window.addEventListener('load', () => setTimeout(() => { map.invalidateSize(); if (allBounds) map.fitBounds(allBounds.pad(0.05)); }, 200));
})();