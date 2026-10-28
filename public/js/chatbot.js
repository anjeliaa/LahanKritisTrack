/* LahanKritisTrack — widget chatbot layanan masyarakat.
   Tombol cepat memakai FAQ tetap (tanpa AI); pertanyaan bebas dikirim ke POST /chatbot (ChatbotController → App\Support\Chatbot). */
(function () {
  const root = document.getElementById('chatbot');
  if (!root) return;
  const $ = (id) => document.getElementById(id);
  const fab = $('cb-toggle'), panel = $('cb-panel'), body = $('cb-body'), chips = $('cb-chips'), form = $('cb-form'), input = $('cb-input');

  const URL = root.dataset.endpoint, CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
  const history = [];   /* percakapan untuk konteks AI (maks. 8 terakhir dikirim) */

  const WELCOME = 'Halo! Saya asisten LahanKritisTrack. Saya dapat membantu Anda memahami informasi lahan kritis dan kegiatan rehabilitasi di Kota Bengkulu. Silakan pilih pertanyaan di bawah atau tulis pertanyaan Anda.';

  /* FAQ tetap untuk tombol cepat (placeholder, bukan hasil AI) */
  const FAQ = [
    { q: 'Apa itu lahan kritis?', a: 'Lahan kritis adalah lahan yang kondisinya sudah menurun sehingga fungsinya sebagai penahan air, penjaga tanah, dan penyedia hasil tanaman berkurang. Pada peta, tingkat kekritisannya dibedakan lewat warna.' },
    { q: 'Cara membaca peta', a: 'Buka bagian Peta Lahan Kritis, lalu klik area berwarna untuk melihat tingkat kekritisan, luas, dan kegiatan pada petak tersebut. Gunakan filter kecamatan atau tingkat kekritisan di atas peta untuk mempersempit tampilan.', links: [{ t: 'Ke peta', h: '#peta' }] },
    { q: 'Kegiatan rehabilitasi', a: 'Daftar kegiatan rehabilitasi yang sudah dipublikasikan tersedia di bagian Kegiatan Rehabilitasi. Anda dapat menyaringnya dengan filter status pada peta.', links: [{ t: 'Lihat kegiatan', h: '#kegiatan' }] },
    { q: 'Hubungi petugas', a: 'Penerusan percakapan ke petugas belum tersedia. Untuk saat ini, silakan hubungi instansi terkait melalui kanal resmi mereka.' }
  ];

  const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

  /* FAQ tetap dijawab lokal; selain itu dikirim ke backend. Mengembalikan Promise<{ text, links? }> */
  function botReply(text) {
    const hit = FAQ.find((f) => f.q.toLowerCase() === text.trim().toLowerCase());
    if (hit) return new Promise((resolve) => setTimeout(() => resolve({ text: hit.a, links: hit.links }), 400));
    const ctrl = new AbortController(), timer = setTimeout(() => ctrl.abort(), 30000);
    return fetch(URL, {
      method: 'POST', signal: ctrl.signal,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF || '' },
      body: JSON.stringify({ message: text, history: history.slice(-8) })
    }).then(async (res) => {
      const j = await res.json().catch(() => ({}));
      if (res.status === 429) {
        return { text: 'Terlalu banyak pertanyaan dalam waktu singkat. Silakan coba lagi sebentar lagi.' };
      }
      if (res.status === 422) {
        return { text: 'Pertanyaan tidak valid. Tulis pertanyaan singkat (maksimal 500 karakter).' };
      }
      
      const answer = typeof j.reply === 'string' ? j.reply : j.text;
      
      if (!res.ok || typeof answer !== 'string' || !answer.trim()) {
        return {
          text: 'Maaf, terjadi gangguan (kode ' + res.status + ')' +
          (j.message ? ': ' + String(j.message).slice(0, 250) : '.')
        };
      }
      
      return { text: answer };

    }).finally(() => clearTimeout(timer));
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
    botReply(text).then((r) => { typing(false); bubble('bot', r.text, r.links); history.push({ role: 'user', text }, { role: 'bot', text: r.text }); })
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