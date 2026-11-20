{{-- Tab pilihan tampilan peta: "Peta Lahan Kritis" atau "Visualisasi Temporal". Dikendalikan public/js/map.js.
     Hanya tampil bila ada lebih dari satu tahun data. --}}
@if($tahunList->count() > 1)
<div class="mtabs" role="tablist" aria-label="Pilih tampilan peta">
    <button type="button" role="tab" class="mtab active" id="tab-peta" data-mtab="peta" aria-selected="true" aria-controls="t-bar">
        <svg class="ic" aria-hidden="true"><use href="#i-map"/></svg> Peta Lahan Kritis
    </button>
    <button type="button" role="tab" class="mtab" id="tab-temporal" data-mtab="temporal" aria-selected="false" aria-controls="t-bar">
        <svg class="ic" aria-hidden="true"><use href="#i-chartline"/></svg> Visualisasi Temporal
    </button>
</div>
<style>
    .mtabs{display:flex;gap:6px;padding:5px;margin:0 0 16px;background:#eef2ee;border:1px solid #e2e9e4;border-radius:14px;width:max-content;max-width:100%}
    .mtab{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border:0;border-radius:10px;background:transparent;font:inherit;font-size:14px;font-weight:700;color:#475569;cursor:pointer;white-space:nowrap}
    .mtab .ic{width:16px;height:16px}
    .mtab:hover{color:#0c3a2b}
    .mtab.active{background:#0c3a2b;color:#fff;box-shadow:0 2px 6px rgba(12,58,43,.25)}
    .mtab:focus-visible{outline:2px solid #f5b83d;outline-offset:2px}
    @media (max-width:640px){.mtabs{width:100%}.mtab{flex:1;justify-content:center;padding:10px 8px;font-size:13px}}
</style>
@endif
