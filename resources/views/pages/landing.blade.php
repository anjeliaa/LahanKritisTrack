@extends('layouts.public')
@section('title', 'LahanKritisTrack — Portal Transparansi Lingkungan Kota Bengkulu')

@section('content')
<section class="hero" id="top">
    <div class="wrap">
        <div>
            <span class="pill"><svg class="ic"><use href="#i-leaf"/></svg> Portal Transparansi Lingkungan Kota Bengkulu</span>
            <h1>Pemantauan Lahan Kritis &amp; Monitoring Program Rehabilitasi</h1>
            <p class="lead">Sistem informasi terbuka yang menghubungkan pemetaan wilayah lahan kritis, capaian kegiatan reboisasi, dan pelaporan transparansi lingkungan bagi seluruh masyarakat Kota Bengkulu.</p>
            <div class="hero-cta">
                <a href="#peta" class="btn btn-primary btn-lg" data-scroll-map><svg class="ic"><use href="#i-pin"/></svg> Jelajahi Peta Lahan Kritis</a>
                <a href="#kegiatan" class="btn btn-dark btn-lg"><svg class="ic"><use href="#i-sprout"/></svg> Lihat Program Rehabilitasi</a>
            </div>
        </div>
        <div class="hero-stats">
            @foreach($landing['stats'] as $s)
            <div class="stat-card">
                <div class="l">{{ $s['label'] }}</div>
                <div class="v tone-{{ $s['tone'] }}">{{ $s['value'] }}</div>
                <div class="s">{{ $s['sub'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section" id="peta">
    <div class="wrap">
        <div class="sec-head">
            <div>
                <div class="eyebrow">Peta Interaktif Spasial</div>
                <h2>Distribusi Wilayah Lahan Kritis</h2>
            </div>
            <p>Cari kecamatan atau kegiatan, saring berdasarkan tingkat kekritisan dan status, lalu klik polygon pada peta untuk melihat detail luas wilayah dan kegiatan terkait.</p>
        </div>

        <div class="card map-card">
            <div class="filters">
                <div class="field">
                    <label for="f-q">Pencarian</label>
                    <div class="search"><svg class="ic"><use href="#i-search"/></svg><input id="f-q" class="input" type="search" placeholder="Cari kecamatan atau nama kegiatan..." autocomplete="off"></div>
                </div>
                <div class="field">
                    <label for="f-kec">Kecamatan</label>
                    <select id="f-kec" class="select" autocomplete="off">
                        <option value="">Semua Kecamatan</option>
                        @foreach($kecamatan as $k)
                        <option value="{{ $k['slug'] }}">{{ $k['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="f-kritis">Tingkat Kekritisan</label>
                    <select id="f-kritis" class="select" autocomplete="off">
                        <option value="">Semua Tingkat</option>
                        @foreach($kritis_opts as $o)
                        <option value="{{ $o['v'] }}">{{ $o['l'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="f-status">Status Kegiatan</label>
                    <select id="f-status" class="select" autocomplete="off">
                        <option value="">Semua Status</option>
                        @foreach($status_opts as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" id="f-reset" class="btn btn-outline" style="height:41px"><svg class="ic"><use href="#i-refresh"/></svg> Reset</button>
            </div>

            <div class="legend">
                <span class="count" id="result-count"></span>
            </div>

            <div class="map-grid">
                <div class="map-box"><div id="map" data-mode="public" data-geojson="{{ route('lahan.geojson') }}"></div></div>
                <div class="info-col">
                    <div class="info-card">
                        <h3 id="kec-nama">Belum ada yang dipilih</h3>
                        <div class="eyebrow" id="kec-sub">KECAMATAN</div>
                        <p class="desc" id="kec-desc">Pilih polygon pada peta untuk melihat detail.</p>
                        <div class="kv-list">
                            <div class="kv"><span>Tingkat Kekritisan:</span><span class="badge" id="kec-badge">-</span></div>
                            <div class="kv"><span>Luas Lahan Kritis:</span><b class="mono" id="kec-luas">-</b></div>
                            <div class="kv"><span>Instansi Pembina:</span><b id="kec-bpdas">-</b></div>
                            <div class="kv"><span>Program Publik:</span><b class="mono" id="kec-jml">-</b></div>
                        </div>
                    </div>
                    <div class="info-card" style="padding:16px">
                        <h3 style="font-size:12px">Kegiatan Rehabilitasi Terbuka</h3>
                        <div class="mini-list" id="kec-kegiatan"></div>
                    </div>
                    <div>
                        <h3 style="font-size:12px;font-weight:700;color:var(--text2)">Daftar hasil pencarian</h3>
                        <div class="result-list" id="result-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="kegiatan">
    <div class="wrap">
        <div class="sec-head">
            <div>
                <div class="eyebrow">Informasi Transparansi</div>
                <h2>Program &amp; Kegiatan Rehabilitasi Dipublikasi</h2>
            </div>
            <p>Daftar mengikuti pencarian dan filter pada peta di atas.</p>
        </div>
        <div class="k-grid">
            @foreach($publik as $k)
            <article class="card k-card" data-kec="{{ $k['kec_slug'] }}" data-kecname="{{ $k['kecamatan'] }}" data-status="{{ $k['status'] }}" data-q="{{ $k['q'] }}">
                <div class="top">
                    <span class="chip">{{ $k['kecamatan'] }} · {{ $k['jenis'] }}</span>
                    <span class="badge b-{{ $k['status_class'] }}">{{ $k['status'] }}</span>
                </div>
                <h3>{{ $k['nama'] }}</h3>
                <p>Dilaksanakan oleh <b>{{ $k['pj'] }}</b>. Target penanganan seluas <b>{{ $k['target_fmt'] }} Ha</b> dimulai pada <b>{{ $k['mulai'] }}</b>.</p>
                <div class="pg">
                    <div class="pg-top"><span>Capaian Progres:</span><b>{{ $k['progres'] }}%</b></div>
                    <div class="progress"><i style="width:{{ $k['progres'] }}%"></i></div>
                </div>
            </article>
            @endforeach
        </div>
        <div class="empty" id="k-empty" style="display:none">Tidak ada program yang cocok dengan filter.</div>
    </div>
</section>

<section class="section" id="statistik">
    <div class="wrap">
        <div class="banner3">
            <div><svg class="ic"><use href="#i-leaf"/></svg><h3>Konservasi Berkelanjutan</h3><p>Menjamin keberlanjutan pemulihan lahan kritis dengan pemantauan terorganisir bersama kelompok tani setempat.</p></div>
            <div><svg class="ic"><use href="#i-shield"/></svg><h3>Data Terverifikasi BPDAS Ketahun</h3><p>Seluruh data spasial bersumber dari hasil inventarisasi instansi resmi BPDAS Ketahun Bengkulu.</p></div>
            <div><svg class="ic"><use href="#i-eye"/></svg><h3>Keterbukaan Publik</h3><p>Masyarakat Kota Bengkulu dapat mengakses realisasi kegiatan reboisasi secara transparan kapan saja.</p></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>window.APP = @json($app);</script>
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('js/map.js') }}"></script>
@endpush
