/* LahanKritisTrack — widget chatbot layanan masyarakat (TAMPILAN SAJA).
   Belum ada backend: jawaban pada tombol cepat adalah teks tetap (FAQ), pertanyaan bebas dijawab pesan placeholder.
   Untuk menyambungkan ke layanan sungguhan, ganti isi botReply() agar memanggil API dan mengembalikan Promise<{text, links?}>. */
(function () {
  const root = document.getElementById('chatbot');
  if (!root) return;
  const $ = (id) => document.getElementById(id);
  const fab = $('cb-toggle'), panel = $('cb-panel'), body = $('cb-body'), chips = $('cb-chips'), form = $('cb-form'), input = $('cb-input');

  const WELCOME = 'Halo! Saya asisten LahanKritisTrack. Saya dapat membantu Anda memahami informasi lahan kritis dan kegiatan rehabilitasi di Kota Bengkulu. Silakan pilih pertanyaan di bawah atau tulis pertanyaan Anda.';

  /* FAQ tetap untuk tombol cepat (placeholder, bukan hasil AI) */
  const FAQ = [
    { q: 'Apa itu lahan kritis?', a: 'Lahan kritis adalah lahan yang kondisinya sudah menurun sehingga fungsinya sebagai penahan air, penjaga tanah, dan penyedia hasil tanaman berkurang. Pada peta, tingkat kekritisannya dibedakan lewat warna.' },
    { q: 'Cara membaca peta', a: 'Buka bagian Peta Lahan Kritis, lalu klik area berwarna untuk melihat tingkat kekritisan, luas, dan kegiatan pada petak tersebut. Gunakan filter kecamatan atau tingkat kekritisan di atas peta untuk mempersempit tampilan.', links: [{ t: 'Ke peta', h: '#peta' }] },
    { q: 'Kegiatan rehabilitasi', a: 'Daftar kegiatan rehabilitasi yang sudah dipublikasikan tersedia di bagian Kegiatan Rehabilitasi. Anda dapat menyaringnya dengan filter status pada peta.', links: [{ t: 'Lihat kegiatan', h: '#kegiatan' }] },
    { q: 'Hubungi petugas', a: 'Penerusan percakapan ke petugas belum tersedia. Untuk saat ini, silakan hubungi instansi terkait melalui kanal resmi mereka.' }
  ];
  const FALLBACK = 'Terima kasih atas pertanyaannya. Asisten otomatis belum terhubung ke layanan jawaban, sehingga pertanyaan bebas belum dapat dijawab. Silakan pilih salah satu pertanyaan cepat di bawah.';

  const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

  /* Titik sambung backend: kembalikan Promise<{ text: string, links?: [{t,h}] }> */
  function botReply(text) {
    const hit = FAQ.find((f) => f.q.toLowerCase() === text.trim().toLowerCase());
    return new Promise((resolve) => setTimeout(() => resolve(hit ? { text: hit.a, links: hit.links } : { text: FALLBACK }), 700));
  }

  function bubble(who, text, links) {
    const row = document.createElement('div'); row.className = 'cb-row ' + who;
    const b = document.createElement('div'); b.className = 'cb-bubble';
    const p = document.createElement('p'); p.textContent = text; b.appendChild(p);   /* textContent: aman dari XSS */
    if (links && links.length) {
      const l = document.createElement('div'); l.className = 'cb-links';
      links.forEach((x) => { const a = document.createElement('a'); a.href = x.h; a.textContent = x.t; a.addEventListener('click', () => { if (window.matchMedia('(max-width:560px)').matches) setOpen(false); }); l.appendChild(a); });
      b.appendChild(l);
    }
    const t = document.createElement('time'); t.textContent = jam(); b.appendChild(t);
    row.appendChild(b); body.appendChild(row);
    body.scrollTop = body.scrollHeight;
    return row;
  }

  let busy = false;
  function typing(on) {
    const old = $('cb-typing'); if (old) old.remove();
    if (!on) return;
    const row = document.createElement('div'); row.className = 'cb-row bot'; row.id = 'cb-typing';
    row.innerHTML = '<div class="cb-bubble cb-typing" aria-label="Asisten sedang mengetik"><i></i><i></i><i></i></div>';
    body.appendChild(row); body.scrollTop = body.scrollHeight;
  }

  function ask(text) {
    text = (text || '').trim();
    if (!text || busy) return;
    busy = true;
    bubble('user', text);
    typing(true);
    botReply(text).then((r) => { typing(false); bubble('bot', r.text, r.links); })
      .catch(() => { typing(false); bubble('bot', 'Maaf, terjadi gangguan. Silakan coba lagi.'); })
      .finally(() => { busy = false; });
  }

  function setOpen(open) {
    panel.hidden = !open;
    root.classList.toggle('open', open);
    fab.setAttribute('aria-expanded', String(open));
    fab.setAttribute('aria-label', open ? 'Tutup chat bantuan' : 'Buka chat bantuan');
    if (open) { if (!body.children.length) bubble('bot', WELCOME); setTimeout(() => input.focus(), 50); }
    else fab.focus();
  }

  FAQ.forEach((f) => {
    const c = document.createElement('button'); c.type = 'button'; c.className = 'cb-chip'; c.textContent = f.q;
    c.addEventListener('click', () => ask(f.q));
    chips.appendChild(c);
  });

  fab.addEventListener('click', () => setOpen(panel.hidden));
  root.querySelector('[data-cb-close]').addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) setOpen(false); });
  form.addEventListener('submit', (e) => { e.preventDefault(); const v = input.value; input.value = ''; ask(v); });
})();
