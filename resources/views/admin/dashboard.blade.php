@extends('layouts.internal')
@section('title', 'Dashboard — LahanKritisTrack')

@section('content')

<section class="stat-grid">
    @foreach($dash['kpi'] as $k)
    <div class="card stat">
        <div class="stat-top">
            <span class="stat-ico {{ $k['tone'] }}"><svg class="ic" aria-hidden="true"><use href="#i-{{ $k['icon'] }}"/></svg></span>
            <span class="stat-l">{{ $k['label'] }}</span>
        </div>
        <div class="stat-v">{{ $k['value'] }}<small>{{ $k['unit'] }}</small></div>
        <span class="stat-sub {{ $k['tone'] }}">{{ $k['sub'] }}</span>
    </div>
    @endforeach
</section>

<section class="dash-grid">
    <div class="card">
        <div class="card-head">
            <h3><svg class="ic"><use href="#i-chartcol"/></svg> Capaian Rehabilitasi per Kecamatan</h3>
            <span class="sub">Target vs Realisasi (Ha)</span>
        </div>
        <div class="chart-body"><canvas id="chart-bar"></canvas></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3><svg class="ic"><use href="#i-pie"/></svg> Distribusi Luas per Tingkat Kekritisan (Ha)</h3>
            <span class="sub">BPDAS Ketahun</span>
        </div>
        <div class="chart-body"><canvas id="chart-donut"></canvas></div>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h3><svg class="ic"><use href="#i-bars"/></svg> Aktivitas Monitoring Terbaru</h3>
            <span class="sub">{{ count($dash['feed']) }} aktivitas terakhir tercatat</span>
        </div>
        <a href="{{ route('admin.monitoring') }}" class="btn btn-soft">Lihat Semua</a>
    </div>

    <div class="act-list">
        @forelse($dash['feed'] as $f)
        <article class="act-row">
            @if($f['foto'])
            <a class="act-thumb" href="{{ $f['foto'] }}" target="_blank" rel="noopener"><img src="{{ $f['foto'] }}" alt="Dokumentasi" loading="lazy"></a>
            @else
            <div class="act-thumb" aria-hidden="true"><svg class="ic"><use href="#i-sprout"/></svg></div>
            @endif

            <div class="act-main">
                <div class="act-title">
                    <b>{{ $f['judul'] }}</b>
                    @if($f['selesai'])<span class="act-tag">SELESAI</span>@endif
                </div>
                <div class="act-desc">{{ $f['catatan'] }}</div>
                <div class="act-meta">
                    <span><svg class="ic" aria-hidden="true"><use href="#i-cal"/></svg>{{ \Carbon\Carbon::parse($f['tanggal'])->locale('id')->translatedFormat('j M Y') }}</span>
                    <span><svg class="ic" aria-hidden="true"><use href="#i-user"/></svg>{{ $f['petugas'] }}</span>
                    <span><svg class="ic" aria-hidden="true"><use href="#i-pin"/></svg>{{ $f['lokasi'] }}</span>
                </div>
            </div>

            <div class="act-prog {{ $f['pill'] }}">
                <span class="act-pct">{{ $f['progres'] }}%</span>
                <div class="act-bar"><i style="width:{{ $f['progres'] }}%"></i></div>
            </div>
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