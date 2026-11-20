@if($tahunList->count() > 1)
{{-- Grafik visualisasi temporal (di bawah peta). Dikendalikan public/js/map.js bersama #t-bar.
     Kiri: tren luas per tahun. Kanan: perbandingan dua tahun (muncul saat tahun pembanding dipilih). --}}
<div class="tcharts" id="t-charts" hidden>
    <div class="tcard tcard-trend">
        <h4>Luas lahan kritis per tahun</h4>
        <p>Klik batang untuk menampilkan tahun itu pada peta. Tidak termasuk kelas Tidak Kritis.</p>
        <div class="tbar-chart"><canvas id="t-chart-tren" role="img" aria-label="Grafik luas lahan kritis per tahun menurut kelas kekritisan"></canvas></div>
    </div>
    <div class="tcard tcard-cmp" id="t-compare" hidden aria-live="polite"></div>
</div>
@endif
