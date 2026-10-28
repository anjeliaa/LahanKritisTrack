{{-- Widget chatbot layanan masyarakat. Logika: public/js/chatbot.js; backend: POST /chatbot (ChatbotController). --}}
<div class="cb" id="chatbot" data-chatbot data-endpoint="{{ route('chatbot.ask') }}">
    <button type="button" class="cb-fab" id="cb-toggle" aria-label="Buka chat bantuan" aria-expanded="false" aria-controls="cb-panel">
        <span class="cb-fab-ic cb-ic-open"><svg viewBox="0 0 24 24" class="ic" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg></span>
        <span class="cb-fab-ic cb-ic-close"><svg class="ic" aria-hidden="true"><use href="#i-x"/></svg></span>
        <span class="cb-fab-tip">Butuh bantuan?</span>
    </button>

    <section class="cb-panel" id="cb-panel" role="dialog" aria-label="Chat bantuan LahanKritisTrack" hidden>
        <header class="cb-head">
            <span class="cb-avatar"><svg class="ic" aria-hidden="true"><use href="#i-leaf"/></svg></span>
            <div class="cb-title">
                <b>Asisten LahanKritis</b>
                <small>Layanan informasi untuk masyarakat</small>
            </div>
            <button type="button" class="cb-x" data-cb-close aria-label="Tutup chat"><svg class="ic" aria-hidden="true"><use href="#i-x"/></svg></button>
        </header>

        <div class="cb-note">Jawaban dibuat oleh AI dari data publik situs ini dan dapat keliru. Untuk urusan resmi, hubungi instansi terkait.</div>

        <div class="cb-body" id="cb-body" role="log" aria-live="polite" aria-label="Percakapan"></div>

        <div class="cb-chips" id="cb-chips" aria-label="Pertanyaan cepat"></div>

        <form class="cb-form" id="cb-form" autocomplete="off">
            <label class="sr-only" for="cb-input">Tulis pertanyaan Anda</label>
            <input id="cb-input" class="cb-input" type="text" maxlength="500" placeholder="Tulis pertanyaan Anda…" autocomplete="off">
            <button type="submit" class="cb-send" aria-label="Kirim pesan"><svg viewBox="0 0 24 24" class="ic" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button>
        </form>
    </section>
</div>
