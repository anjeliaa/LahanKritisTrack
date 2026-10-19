
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
                Aktivitas monitoring terbaru
            </h3>
            <span class="sub">Pembaruan lapangan dan jejak verifikasi</span>
        </div>
        <a href="{{ route('admin.monitoring') }}" class="btn btn-outline">
            Lihat semua aktivitas
            <svg class="ic"><use href="#i-arrowright"/></svg>
        </a>
    </div>

    <div class="feed">
        @forelse($dash['feed'] as $f)
        <div class="feed-row">
            <div class="feed-time">
                <b>{{ $f['jam'] }}</b>
                <small>{{ $f['tgl'] }}</small>
            </div>

            <div class="feed-main">
                <span class="feed-ico {{ $f['tone'] }}">
                    <svg class="ic">
                        <use href="#i-{{ $f['icon'] }}"/>
                    </svg>
                </span>
                <div>
                    <b>{{ $f['judul'] }}</b>
                    <small>{{ $f['sub'] }}</small>
                </div>
            </div>

            <div class="feed-who">{{ $f['petugas'] }}</div>

            <div>
                <span class="pillst {{ $f['tone'] }}">
                    {{ $f['status'] }}
                </span>
            </div>

            <button type="button" class="dots" aria-label="Menu">
                <svg class="ic"><use href="#i-dots"/></svg>
            </button>
        </div>
        @empty
        <div class="empty" style="padding:24px">
            Belum ada aktivitas monitoring.
        </div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>
    window.APP = @json($app);

    // Memastikan data grafik tersedia sebelum dashboard.js dijalankan.
    window.APP = window.APP || {};
    window.APP.dashboard = window.APP.dashboard || {};

    console.log('Data dashboard:', window.APP);
    console.log('Data grafik:', window.APP.dashboard);
</script>

<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
