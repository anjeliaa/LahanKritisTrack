@extends('layouts.internal')
@section('title', 'Dashboard — LahanKritisTrack')

@section('content')
<section class="kpi-grid">
    @foreach($dash['kpi'] as $k)
    <div class="card kpi">
        <div class="ico {{ $k['tone'] }}">
            <svg class="ic">
                <use href="#i-{{ $k['icon'] }}"/>
            </svg>
        </div>
        <div>
            <div class="l">{{ $k['label'] }}</div>
            <div class="v">
                {{ $k['value'] }}<small>{{ $k['unit'] }}</small>
            </div>
            <div class="s {{ $k['tone'] }}">{{ $k['sub'] }}</div>
        </div>
    </div>
    @endforeach
</section>

<section class="charts">
    <div class="card">
        <div class="card-head">
            <h3>
                <svg class="ic"><use href="#i-pie"/></svg>
                Distribusi Luas per Tingkat Kekritisan (Ha)
            </h3>
            <span class="sub">BPDAS Ketahun</span>
        </div>
        <div class="chart-body">
            <canvas id="chart-donut"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>
                <svg class="ic"><use href="#i-chartcol"/></svg>
                Capaian Rehabilitasi per Kecamatan
            </h3>
            <span class="sub">Target vs Realisasi</span>
        </div>
        <div class="chart-body">
            <canvas id="chart-bar"></canvas>
        </div>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h3>
                <svg class="ic"><use href="#i-bars"/></svg>
                Timeline Aktivitas Monitoring Lapangan Terbaru
            </h3>
            <span class="sub">{{ count($dash['feed']) }} aktivitas terakhir tercatat</span>
        </div>
        <a href="{{ route('admin.monitoring') }}" class="btn btn-outline tl2-all">Lihat Semua</a>
    </div>

    <div class="tl2">
        @forelse($dash['feed'] as $f)
        <article class="tl2-row">
            <div class="tl2-date {{ $f['chip'] }}">
                <svg class="ic" aria-hidden="true"><use href="#i-cal"/></svg>
                <b>{{ $f['tanggal'] }}</b>
                <i class="tl2-dot {{ $f['chip'] }}" aria-hidden="true"></i>
            </div>

            @if($f['foto'])
            <a class="tl2-thumb" href="{{ $f['foto'] }}" target="_blank" rel="noopener"><img src="{{ $f['foto'] }}" alt="Dokumentasi" loading="lazy"></a>
            @else
            <div class="tl2-thumb" aria-hidden="true"></div>
            @endif

            <div class="tl2-main">
                <div class="tl2-title">
                    <b>{{ $f['judul'] }}</b>
                    @if($f['selesai'])<span class="tl2-tag">SELESAI</span>@endif
                </div>
                <div class="tl2-desc">{{ $f['catatan'] }}</div>
                <div class="tl2-meta">
                    <span><svg class="ic" aria-hidden="true"><use href="#i-user"/></svg> {{ $f['petugas'] }}</span>
                    <span><svg class="ic" aria-hidden="true"><use href="#i-pin"/></svg> {{ $f['lokasi'] }}</span>
                </div>
            </div>

            <span class="tl2-pill {{ $f['pill'] }}">{{ $f['progres'] }}%</span>
            <div class="tl2-bar {{ $f['pill'] }}"><i style="width:{{ $f['progres'] }}%"></i></div>
        </article>
        @empty
        <div class="empty" style="padding:24px">Belum ada aktivitas monitoring.</div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>window.APP = @json($app);</script>
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
