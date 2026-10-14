/* LahanKritisTrack — peta interaktif (Leaflet) untuk halaman publik & petugas */
(function () {
  const el = document.getElementById('map');
  if (!el || !window.L) return;
  const APP = window.APP || { kegiatan: [], kecamatan: [] };
  const mode = el.dataset.mode || 'public';
  const COL = { sangat: '#ef4444', kritis: '#f97316', agak: '#eab308', potensial: '#10b981' };
  const $ = (id) => document.getElementById(id);
  const norm = (s) => (s || '').toLowerCase();

  const map = L.map(el, { zoomControl: true, scrollWheelZoom: false }).setView([-3.82, 102.3], 12);
  const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
  const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom: 17, attribution: '&copy; OpenTopoMap (CC-BY-SA)' });
  L.control.layers({ 'Peta jalan (OSM)': osm, 'Topografi': topo }, null, { position: 'topright' }).addTo(map);
  L.control.scale({ imperial: false }).addTo(map);
  el.addEventListener('click', () => map.scrollWheelZoom.enable());
  el.addEventListener('mouseleave', () => map.scrollWheelZoom.disable());

  const layers = {};
  let selected = null;
  let allBounds = null;

  function kegOf(nama) { return APP.kegiatan.filter((k) => k.kecamatan === nama); }
  function baseStyle(f, on) {
    const c = COL[f.properties.kritis_class];
    return on ? { color: '#ffffff', weight: 2, fillColor: c, fillOpacity: 0.6 } : { color: '#cbd5e1', weight: 1, fillColor: c, fillOpacity: 0.08 };
  }

  function select(slug) {
    Object.values(layers).forEach((l) => l.setStyle({ dashArray: null }));
    const lyr = layers[slug]; if (!lyr) return;
    selected = slug;
    const p = lyr.feature.properties;
    lyr.setStyle({ color: '#052e16', weight: 3, dashArray: '6 4' }); lyr.bringToFront();
    const kg = kegOf(p.nama);
    $('kec-nama').textContent = p.nama;
    $('kec-sub').textContent = 'KECAMATAN · ' + p.nama.toUpperCase();
    $('kec-desc').innerHTML = 'Area lahan kritis seluas <b>' + p.luas.toFixed(1) + ' Ha</b> dengan tingkat kekritisan <b>' + p.kritis.toLowerCase() + '</b>.';
    const b = $('kec-badge'); b.textContent = p.kritis; b.className = 'badge b-' + p.kritis_class;
    $('kec-luas').textContent = p.luas.toFixed(1) + ' Ha';
    $('kec-bpdas').textContent = p.bpdas;
    $('kec-jml').textContent = kg.length + ' Kegiatan';
    $('kec-kegiatan').innerHTML = kg.length ? kg.map((k) => '<div class="mini-item"><span>' + k.nama + '</span><span class="badge b-' + k.status_class + '">' + k.status + '</span></div>').join('') : '<div class="empty">Belum ada kegiatan rehabilitasi pada kecamatan ini.</div>';
    document.querySelectorAll('.result-item').forEach((r) => r.classList.toggle('on', r.dataset.slug === slug));
    const add = $('btn-add-here'); if (add) add.dataset.fill = JSON.stringify({ kecamatan: p.nama });
  }

  function matches(p) {
    const q = norm($('f-q') && $('f-q').value).trim(), kec = $('f-kec') && $('f-kec').value, kr = $('f-kritis') && $('f-kritis').value, st = $('f-status') && $('f-status').value;
    const kg = kegOf(p.nama);
    const okQ = !q || norm(p.nama).includes(q) || kg.some((k) => norm(k.nama + ' ' + k.jenis + ' ' + k.pj).includes(q));
    return okQ && (!kec || kec === p.slug) && (!kr || kr === p.kritis_class) && (!st || kg.some((k) => k.status === st));
  }

  function apply(fit) {
    let n = 0; const ok = [];
    Object.values(layers).forEach((l) => {
      const m = matches(l.feature.properties);
      l.setStyle(baseStyle(l.feature, m)); l.options.interactive = true;
      if (m) { n++; ok.push(l); }
      l.feature._ok = m;
    });
    if (selected && layers[selected]) select(selected);
    const rc = $('result-count'); if (rc) rc.innerHTML = '<b>' + n + '</b> dari ' + Object.keys(layers).length + ' kecamatan ditemukan';
    const rl = $('result-list');
    if (rl) {
      rl.innerHTML = ok.length ? ok.map((l) => { const p = l.feature.properties; return '<button type="button" class="result-item' + (selected === p.slug ? ' on' : '') + '" data-slug="' + p.slug + '"><span><b>' + p.nama + '</b> <small>' + p.luas.toFixed(1) + ' Ha</small></span><span class="badge b-' + p.kritis_class + '">' + p.kritis + '</span></button>'; }).join('') : '<div class="empty">Tidak ada kecamatan yang cocok. Ubah kata kunci atau filter.</div>';
    }
    const st = $('f-status') && $('f-status').value, q = norm($('f-q') && $('f-q').value).trim();
    const okSlugs = ok.map((l) => l.feature.properties.slug);
    let cs = 0;
    document.querySelectorAll('.k-card').forEach((c) => {
      const show = okSlugs.includes(c.dataset.kec) && (!st || c.dataset.status === st) && (!q || c.dataset.q.includes(q) || norm(c.dataset.kecname).includes(q));
      c.style.display = show ? '' : 'none'; if (show) cs++;
    });
    const ke = $('k-empty'); if (ke) ke.style.display = cs ? 'none' : '';
    if (fit && ok.length) map.fitBounds(L.featureGroup(ok).getBounds().pad(0.2), { maxZoom: 13 });
  }

  fetch(el.dataset.geojson).then((r) => r.json()).then(function (gj) {
    L.geoJSON(gj, {
      style: (f) => baseStyle(f, true),
      onEachFeature: function (f, lyr) {
        layers[f.properties.slug] = lyr;
        lyr.bindTooltip(f.properties.nama, { permanent: true, direction: 'center', className: 'kec-label' });
        lyr.on({
          click: () => select(f.properties.slug),
          mouseover: () => { if (selected !== f.properties.slug && f._ok !== false) lyr.setStyle({ fillOpacity: 0.8 }); },
          mouseout: () => { if (selected !== f.properties.slug) lyr.setStyle({ fillOpacity: f._ok === false ? 0.08 : 0.6 }); }
        });
      }
    }).addTo(map);
    allBounds = L.featureGroup(Object.values(layers)).getBounds();
    map.fitBounds(allBounds.pad(0.05));
    select('selebar'); apply(false);
  });

  ['f-q', 'f-kec', 'f-kritis', 'f-status'].forEach((id) => { const i = $(id); if (i) i.addEventListener('input', () => apply(true)); });
  const rs = $('f-reset'); if (rs) rs.addEventListener('click', () => { ['f-q', 'f-kec', 'f-kritis', 'f-status'].forEach((id) => { const i = $(id); if (i) i.value = ''; }); apply(true); });
  document.addEventListener('click', (e) => { const r = e.target.closest('.result-item'); if (r) { select(r.dataset.slug); const l = layers[r.dataset.slug]; if (l) map.fitBounds(l.getBounds().pad(0.6), { maxZoom: 14 }); } });
  const sc = document.querySelectorAll('[data-scroll-map]'); sc.forEach((a) => a.addEventListener('click', () => setTimeout(() => map.invalidateSize(), 400)));
  window.addEventListener('load', () => setTimeout(() => { map.invalidateSize(); if (allBounds) map.fitBounds(allBounds.pad(0.05)); }, 200));
})();
