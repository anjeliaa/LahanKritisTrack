/* Halaman masyarakat: interaksi tambahan (tidak mengubah logika peta di map.js). */
(function () {
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  /* header: transparan di atas hero, solid setelah digulir */
  const header = $('#lp-header');
  const onScroll = () => header && header.classList.toggle('scrolled', window.scrollY > 40);
  onScroll(); window.addEventListener('scroll', onScroll, { passive: true });

  /* menu HP */
  const burger = $('#lp-burger');
  if (burger && header) {
    burger.addEventListener('click', () => {
      const open = header.classList.toggle('nav-open');
      burger.setAttribute('aria-expanded', String(open));
      burger.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    });
    $$('#lp-nav a').forEach((a) => a.addEventListener('click', () => { header.classList.remove('nav-open'); burger.setAttribute('aria-expanded', 'false'); }));
  }

  /* terapkan nilai ke filter peta (memicu logika map.js lewat event input) lalu gulir ke peta */
  function setFilter(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }
  const toMap = () => { const m = document.getElementById('peta'); if (m) m.scrollIntoView({ behavior: 'smooth', block: 'start' }); };

  /* pencarian di hero -> kolom pencarian peta */
  const hs = $('[data-hero-search]');
  if (hs) hs.addEventListener('submit', (e) => {
    e.preventDefault();
    setFilter('f-q', hs.elements.q.value.trim());
    setTimeout(toMap, 30);
  });

  /* kartu warna -> saring peta berdasarkan tingkat kekritisan */
  $$('[data-kelas]').forEach((a) => a.addEventListener('click', (e) => {
    e.preventDefault();
    setFilter('f-kritis', a.dataset.kelas);
    setTimeout(toMap, 30);
  }));

  /* carousel kegiatan */
  const track = $('#lp-track');
  $$('[data-carousel]').forEach((b) => b.addEventListener('click', () => {
    if (!track) return;
    const card = $('.lp-kcard', track);
    const step = (card ? card.getBoundingClientRect().width : 340) + 22;
    track.scrollBy({ left: b.dataset.carousel === 'next' ? step : -step, behavior: 'smooth' });
  }));

  /* tombol "Mulai Bertanya" membuka chatbot */
  $$('[data-open-chatbot]').forEach((b) => b.addEventListener('click', () => {
    const panel = document.getElementById('cb-panel'), fab = document.getElementById('cb-toggle');
    if (fab && panel && panel.hidden) fab.click();
  }));
})();
