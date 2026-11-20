{{-- Tahun data pada peta (visualisasi temporal), bagian kontrol (tahun, putar, banding, slider) tetap di atas peta; grafik ada di partial map-temporal-charts. Dipakai public/js/map.js.
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


</div>

<style>
    .tbar[hidden],.tcharts[hidden],#kec-kegiatan-card[hidden]{display:none!important}
    .tbar{margin:0 0 14px;padding:16px 20px;background:#f6f8f6;border:1px solid #e2e9e4;border-radius:14px}
    .tbar-top{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
    .tbar-title{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
    .tbar-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .tbar-year{font-size:26px;line-height:1;color:#0c3a2b}
    .tbar-note{font-size:12px;color:#64748b}
    .tbar-actions{display:flex;flex-wrap:wrap;align-items:center;gap:12px}
    .tbar-play{height:38px;min-width:92px}
    .tbar-cmp{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#334155;white-space:nowrap}
    .tbar .tbar-cmp .select{height:38px;width:auto;min-width:190px;line-height:normal;padding:0 34px 0 12px;box-sizing:border-box}
    .tbar-slider{margin-top:12px}
    .tbar-slider input[type=range]{width:100%;accent-color:#0c3a2b;cursor:pointer}
    .tbar-ticks{display:flex;justify-content:space-between;font-size:11px;color:#64748b;font-variant-numeric:tabular-nums;margin-top:2px}

    /* grafik: dua kartu sejajar di bawah peta */
    .tcharts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:16px 0 0;align-items:stretch}
    .tcard{background:#fff;border:1px solid #e2e9e4;border-radius:14px;padding:16px 18px;min-width:0;display:flex;flex-direction:column}
    .tcard-trend{grid-column:1;grid-row:1}
    .tcard-cmp{grid-column:2;grid-row:1}
    .tcard h4{margin:0 0 4px;font-size:14px;color:#0c3a2b}
    .tcard p{margin:0 0 12px;font-size:12px;color:#64748b}
    .tcard-cmp[hidden]{display:flex;align-items:center;justify-content:center;text-align:center;background:#f6f8f6;border-style:dashed;border-color:#cbd5d0}
    .tcard-cmp[hidden]:empty::before{content:"Pilih tahun pada \"Bandingkan dengan\" untuk menampilkan perbandingan di sini.";max-width:240px;font-size:13px;line-height:1.5;color:#64748b}
    .tbar-chart{position:relative;height:280px}
    .tbar-chart-sm{height:200px}
    .tbar-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:0 0 12px}
    .tbar-stats>div{background:#f6f8f6;border:1px solid #e2e9e4;border-radius:10px;padding:8px 10px;display:flex;flex-direction:column;gap:2px;min-width:0}
    .tbar-stats span{font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:#64748b}
    .tbar-stats b{font-size:16px;color:#0c3a2b;font-variant-numeric:tabular-nums}
    .tbar-stats small{font-size:11px;color:#64748b}
    .tbar-foot{margin:10px 0 0!important}
    .tbar-stats small.tbar-up,.tbar-stats small.tbar-down{font-weight:700}
    .tbar-up{color:#b45309}
    .tbar-down{color:#15803d}
    @media (max-width:820px){
        .tbar{padding:14px}
        .tbar-actions{width:100%}
        .tbar-cmp{flex:1;white-space:normal}
        .tbar .tbar-cmp .select{flex:1;min-width:0}
        .tcharts{grid-template-columns:minmax(0,1fr)}
        .tcard-trend,.tcard-cmp{grid-column:1;grid-row:auto}
        .tcard-trend{order:1}.tcard-cmp{order:2}
        .tcard-cmp[hidden]{min-height:110px}
    }
</style>
@endif
