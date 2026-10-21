/* Tambah data petak lahan secara manual: gambar polygon dengan klik pada peta (Leaflet), atau tempel GeoJSON.
   Mengisi <textarea name="geometri"> dengan GeoJSON Polygon; server menghitung luas bila kolom Luas kosong. */
(function () {
  const bd = document.getElementById('modal-lahan-tambah');
  if (!bd || !window.L) return;
  const $ = (id) => document.getElementById(id);
  const el = $('lahan-draw-map'), ta = $('draw-geometri'), info = $('draw-info');
  let map, poly, dots, pts = [];

  /* luas poligon bola (Ha) — rumus yang sama dengan penghitungan di server */
  function areaHa(ring) {
    const R = 6378137, rad = (d) => d * Math.PI / 180; let s = 0;
    for (let i = 0; i < ring.length - 1; i++) s += rad(ring[i + 1][0] - ring[i][0]) * (2 + Math.sin(rad(ring[i][1])) + Math.sin(rad(ring[i + 1][1])));
    return Math.abs(s * R * R / 2) / 10000;
  }

  function render() {
    poly.setLatLngs(pts);
    dots.clearLayers();
    pts.forEach((p) => L.circleMarker(p, { radius: 4, color: '#15803d', weight: 2, fillColor: '#fff', fillOpacity: 1, interactive: false }).addTo(dots));
    if (pts.length >= 3) {
      const ring = pts.map((p) => [+p.lng.toFixed(6), +p.lat.toFixed(6)]); ring.push(ring[0]);
      ta.value = JSON.stringify({ type: 'Polygon', coordinates: [ring] });
      info.textContent = pts.length + ' titik · ≈ ' + areaHa(ring).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Ha';
    } else {
      ta.value = '';
      info.textContent = pts.length ? pts.length + ' titik (minimal 3)' : 'Belum ada titik';
    }
  }

  function init() {
    if (map) return;
    map = L.map(el, { zoomControl: true, scrollWheelZoom: true }).setView([-3.82, 102.3], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    poly = L.polygon([], { color: '#16a34a', weight: 2, fillColor: '#16a34a', fillOpacity: 0.25 }).addTo(map);
    dots = L.layerGroup().addTo(map);
    map.on('click', (e) => { pts.push(e.latlng); render(); });
    window.LKT_lahanDraw = { map, get points() { return pts.slice(); } };   /* titik uji & debugging */
  }

  /* menempel GeoJSON -> tampilkan bentuknya pada peta */
  ta.addEventListener('change', () => {
    let g; try { g = JSON.parse(ta.value); } catch (e) { return; }
    if (g && g.type === 'Feature') g = g.geometry;
    if (g && g.type === 'FeatureCollection') g = g.features && g.features[0] && g.features[0].geometry;
    if (!g || !g.coordinates) return;
    const ring = g.type === 'Polygon' ? g.coordinates[0] : (g.type === 'MultiPolygon' ? g.coordinates[0][0] : null);
    if (!ring) return;
    pts = ring.slice(0, ring[0].join() === ring[ring.length - 1].join() ? -1 : undefined).map((c) => L.latLng(c[1], c[0]));
    const keep = ta.value; render(); ta.value = keep;
    if (pts.length) map.fitBounds(poly.getBounds(), { padding: [20, 20] });
  });

  $('draw-undo').addEventListener('click', () => { pts.pop(); render(); });
  $('draw-reset').addEventListener('click', () => { pts = []; render(); });

  /* modal dibuka: peta dibuat sekali, gambar dikosongkan, ukuran peta dihitung ulang */
  bd.addEventListener('modal:open', () => {
    init(); pts = []; render();
    map.setView([-3.82, 102.3], 12);
    setTimeout(() => map.invalidateSize(), 80);
  });
})();
