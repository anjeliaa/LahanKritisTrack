{{-- Tahun data pada peta (visualisasi temporal). Dipakai public/js/map.js.
     Variabel: $tahunList (koleksi tahun, urut naik), $tahunUtama (tahun data terbaru) --}}
@if($tahunList->count() > 1)
<div class="tbar" id="t-bar" role="tabpanel" aria-labelledby="tab-temporal" hidden
     data-geojson="{{ route('lahan.geojson') }}"
     data-ringkas="{{ route('lahan.temporal') }}"
     data-utama="{{ $tahunUtama }}"
     data-tahun='@json($tahunList)'>

    <div class="tbar-top">
        <div class="tbar-title">
            <span class="tbar-label">Tahun data</span>
            <b class="tbar-year" id="t-label">{{ $tahunUtama }}</b>
            <span class="tbar-note" id="t-note">Data terbaru</span>
        </div>

        <div class="tbar-actions">
            <button type="button" id="t-play" class="btn btn-outline tbar-play" aria-pressed="false">
                <span id="t-play-ic" aria-hidden="true">▶</span> <span id="t-play-tx">Putar</span>
            </button>

            <label class="tbar-cmp" for="t-banding">
                <span>Bandingkan dengan</span>
                <select id="t-banding" class="select" autocomplete="off">
                    <option value="">Tidak dibandingkan</option>
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="tbar-slider">
        <input type="range" id="t-range" min="0" max="{{ $tahunList->count() - 1 }}" step="1"
               value="{{ $tahunList->count() - 1 }}" aria-label="Tahun data peta">
        <div class="tbar-ticks" aria-hidden="true">
            @foreach($tahunList as $t)<span>{{ $t }}</span>@endforeach
        </div>
    </div>

    <div class="tbar-cmp-box" id="t-compare" hidden aria-live="polite"></div>
</div>

<style>
    .tbar[hidden]{display:none}
    .tbar{margin:0 0 16px;padding:16px 18px;background:#f6f8f6;border:1px solid #e2e9e4;border-radius:14px}
    .tbar-top{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
    .tbar-title{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
    .tbar-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .tbar-year{font-size:26px;line-height:1;color:#0c3a2b}
    .tbar-note{font-size:12px;color:#64748b}
    .tbar-actions{display:flex;flex-wrap:wrap;align-items:center;gap:12px}
    .tbar-play{height:38px;min-width:92px}
    .tbar-cmp{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#334155}
    .tbar-cmp .select{height:38px;min-width:150px}
    .tbar-slider{margin-top:12px}
    .tbar-slider input[type=range]{width:100%;accent-color:#0c3a2b;cursor:pointer}
    .tbar-ticks{display:flex;justify-content:space-between;font-size:11px;color:#64748b;font-variant-numeric:tabular-nums;margin-top:2px}
    .tbar-cmp-box{margin-top:14px;padding-top:14px;border-top:1px solid #e2e9e4}
    .tbar-cmp-box h4{margin:0 0 4px;font-size:14px;color:#0c3a2b}
    .tbar-cmp-box p{margin:0 0 10px;font-size:12px;color:#64748b}
    .tbar-table{width:100%;border-collapse:collapse;font-size:12.5px}
    .tbar-table th,.tbar-table td{padding:7px 10px;text-align:right;border-bottom:1px solid #e2e9e4;font-variant-numeric:tabular-nums}
    .tbar-table th:first-child,.tbar-table td:first-child{text-align:left}
    .tbar-table thead th{font-size:11px;letter-spacing:.04em;text-transform:uppercase;color:#64748b}
    .tbar-table tfoot td{font-weight:700;border-bottom:0}
    .tbar-up{color:#b45309}
    .tbar-down{color:#15803d}
    .tbar-dot{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px;vertical-align:-1px;border:1px solid rgba(0,0,0,.25)}
    @media (max-width:640px){
        .tbar{padding:14px}
        .tbar-actions{width:100%}
        .tbar-cmp{width:100%;justify-content:space-between}
        .tbar-cmp .select{flex:1}
        .tbar-table{font-size:11.5px}
        .tbar-table th,.tbar-table td{padding:6px 6px}
    }
</style>
@endif
