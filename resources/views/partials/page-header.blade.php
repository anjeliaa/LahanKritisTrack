@php
    $adaStat = ! empty($ph['stats']);

    // ikon kartu statistik menurut labelnya
    $ikonLabel = [
        // data lahan & peta
        'petak lahan' => 'map', 'total luas' => 'image', 'kritis' => 'flag', 'potensial' => 'trendup',
        // kegiatan
        'total kegiatan' => 'sprout', 'berjalan' => 'shovel', 'selesai' => 'checkc', 'rencana' => 'cal',
        // monitoring
        'total sesi' => 'clipcheck', 'rata-rata progres' => 'chartline', 'petugas aktif' => 'users',
        'kondisi' => 'leaf', 'terpantau' => 'eye',
        // akun
        'total akun' => 'users', 'admin' => 'shield', 'petugas' => 'user',
        // laporan & lainnya
        'dilaporkan' => 'filetext', 'periode' => 'cal',
        'tahun anggaran' => 'cal', 'cakupan' => 'pin', 'sumber data' => 'sheet',
    ];
    // cadangan bila ada label baru: ikon menurut warna kartu
    $ikonTone = ['white' => 'bars', 'green' => 'leaf', 'red' => 'flag', 'blue' => 'chartcol', 'amber' => 'clipcheck'];
@endphp

<section class="page-header {{ $adaStat ? 'has-stats' : '' }}">
    <div class="ph-left">
        <div class="ph-icon"><svg class="ic" aria-hidden="true"><use href="#i-{{ $ph['icon'] }}"/></svg></div>
        <div>
            <div class="ph-path">{{ $ph['path'] }}</div>
            <h1 class="ph-title">{{ $ph['title'] }}</h1>
            <p class="ph-desc">{{ $ph['desc'] }}</p>
        </div>
    </div>
</section>

@if($adaStat)
<section class="hb-stats">
    @foreach($ph['stats'] as $s)
    @php $ikon = $ikonLabel[mb_strtolower($s['label'])] ?? ($ikonTone[$s['tone']] ?? 'bars'); @endphp
    <div class="card stat">
        <div class="stat-top">
            <span class="stat-ico {{ $s['tone'] }}"><svg class="ic" aria-hidden="true"><use href="#i-{{ $ikon }}"/></svg></span>
            <span class="stat-l">{{ $s['label'] }}</span>
        </div>
        <div class="stat-v">{{ $s['value'] }}</div>
        <span class="stat-sub {{ $s['tone'] }}">{{ $s['sub'] }}</span>
    </div>
    @endforeach
</section>
@endif