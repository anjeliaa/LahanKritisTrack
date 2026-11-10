@extends('layouts.public')
@section('title', 'LahanKritisTrack — Peta dan Monitoring Rehabilitasi Lahan')

@section('content')
@php
    $heroFoto = file_exists(public_path('img/hero.jpg')) ? asset('img/hero.jpg') : null;
    $ikonKelas = ['tidak' => 'leaf', 'pulih' => 'checkc', 'potensial' => 'eye', 'agak' => 'flag', 'kritis' => 'flag', 'sangat' => 'flag'];
    $ikonStat = ['pin', 'sprout', 'shovel', 'shield'];
@endphp

{{-- ===================================================== HERO --}}
<section class="lp-hero" id="top" @if($heroFoto) style="background-image:linear-gradient(90deg,rgba(5,46,35,.72),rgba(5,46,35,.25)),url('{{ $heroFoto }}')" @endif>
    <div class="lp-hero-in">
        <div class="lp-hero-copy">
            <h1>Memantau Lahan Kritis,<br><em>Memulihkan</em> Bumi Bengkulu</h1>
            <p class="lp-lead">Lihat sebaran lahan kritis, ikuti kemajuan kegiatan rehabilitasi, dan pantau hasilnya — terbuka untuk seluruh masyarakat Kota Bengkulu.</p>


            <div class="lp-cta-row">
                <a href="#peta" class="lp-btn lp-btn-sun" data-scroll-map><svg class="ic" aria-hidden="true"><use href="#i-pin"/></svg> Jelajahi Peta</a>
                <a href="#kegiatan" class="lp-btn lp-btn-ghost"><svg class="ic" aria-hidden="true"><use href="#i-sprout"/></svg> Lihat Program Rehabilitasi</a>
            </div>
        </div>
    </div>
    <svg class="lp-wave" viewBox="0 0 1440 140" preserveAspectRatio="none" aria-hidden="true"><path d="M0,70 C200,10 360,10 560,62 C760,114 940,116 1140,60 C1260,28 1360,24 1440,44 L1440,140 L0,140 Z"/></svg>
</section>

{{-- ===================================================== CARA MEMBACA WARNA --}}
@if(count($kelas))
<section class="lp-kelas" aria-labelledby="kelas-judul">
    <h2 class="sr-only" id="kelas-judul">Cara membaca warna peta</h2>
    <div class="lp-kelas-grid">
        @foreach($kelas as $k)
        <a class="lp-kelas-item" href="#peta" data-scroll-map data-kelas="{{ $k['v'] }}" style="--c: {{ $k['warna'] }}">
            <span class="lp-kelas-dot"><svg class="ic" aria-hidden="true"><use href="#i-{{ $ikonKelas[$k['v']] ?? 'leaf' }}"/></svg></span>
            <b>{{ $k['label'] }}</b>
            <span class="lp-kelas-stat">{{ $k['n'] }} petak · {{ number_format($k['luas'], 1) }} Ha</span>
            <small>{{ $k['desc'] }}</small>
        </a>
        @endforeach
    </div>
    <p class="lp-kelas-note">Klik salah satu untuk menyaring peta. Tingkat kekritisan mengikuti data resmi BPDAS; lahan yang kegiatan rehabilitasinya sudah selesai ditandai <b>Telah Direhabilitasi</b>.</p>
</section>
@endif

{{-- ===================================================== DAMPAK --}}
<section class="lp-impact" aria-labelledby="dampak-judul">
    <svg class="lp-impact-wave top" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0 L1440,0 L1440,30 C1180,84 940,4 720,40 C500,76 260,8 0,56 Z"/></svg>
    <div class="lp-wrap">
        <div class="lp-impact-head">
            <span class="lp-script" id="dampak-judul">Dampak Nyata</span>
            <p>Angka terkini dari data lahan dan kegiatan rehabilitasi di Kota Bengkulu.</p>
        </div>
        <div class="lp-stats">
            @foreach($landing['stats'] as $s)
            <div class="lp-stat">
                <span class="lp-stat-ic"><svg class="ic" aria-hidden="true"><use href="#i-{{ $ikonStat[$loop->index] ?? 'leaf' }}"/></svg></span>
                <div class="lp-stat-v">{{ $s['value'] }}</div>
                <div class="lp-stat-l">{{ $s['label'] }}</div>
                <div class="lp-stat-s">{{ $s['sub'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    <svg class="lp-impact-wave bottom" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0,90 L1440,90 L1440,46 C1200,4 980,84 720,48 C460,12 240,80 0,36 Z"/></svg>
</section>

{{-- ===================================================== TENTANG --}}
<section class="lp-about">
    <div class="lp-blob b1" aria-hidden="true"></div>
    <div class="lp-wrap lp-about-grid">
        <div class="lp-about-art" aria-hidden="true">
            <div class="lp-art-shape">
                <svg viewBox="0 0 400 400" preserveAspectRatio="xMidYMid slice">
                    <defs><linearGradient id="ab" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#bfe6da"/><stop offset="1" stop-color="#f6efcf"/></linearGradient></defs>
                    <rect width="400" height="400" fill="url(#ab)"/>
                    <circle cx="306" cy="104" r="74" fill="#fff3b8" opacity=".4"/><circle cx="306" cy="104" r="42" fill="#fff0a6"/>
                    <path d="M0,292 C80,246 160,276 240,248 C310,222 360,246 400,232 L400,400 L0,400Z" fill="#8fd0a6"/>
                    <path d="M0,332 C90,298 170,328 250,306 C320,288 370,308 400,298 L400,400 L0,400Z" fill="#49a574"/>
                    <ellipse cx="200" cy="356" rx="128" ry="34" fill="#6b4a2f"/><ellipse cx="200" cy="348" rx="102" ry="23" fill="#85603c"/>
                    <path d="M200,344 C200,292 198,252 204,204" stroke="#2f7d4f" stroke-width="9" fill="none" stroke-linecap="round"/>
                    <path d="M203,238 C148,238 114,200 108,158 C156,158 196,186 203,238Z" fill="#52b878"/>
                    <path d="M205,212 C252,212 290,178 300,130 C252,128 212,158 205,212Z" fill="#2f9a5c"/>
                    <path d="M203,238 C176,214 150,190 126,170 M205,212 C230,186 262,160 284,144" stroke="#fff" stroke-opacity=".35" stroke-width="2" fill="none"/>
                    <g fill="#fff" opacity=".8"><path d="M96 96l4 12 12 4-12 4-4 12-4-12-12-4 12-4z"/><path d="M334 214l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/></g>
                </svg>
            </div>
            <span class="lp-script lp-art-note">Pulih bersama,<br>tumbuh bersama</span>
        </div>

        <div class="lp-about-copy">
            <span class="lp-eyebrow">Tentang Portal</span>
            <h2>Satu portal untuk memahami dan mengawasi pemulihan lahan</h2>
            <p>LahanKritisTrack menghubungkan informasi lokasi lahan kritis dengan pengelolaan kegiatan rehabilitasi, pemantauan lapangan, dan pelaporan — agar kemajuannya dapat dilihat siapa saja.</p>
            <ul class="lp-values">
                <li><span><svg class="ic" aria-hidden="true"><use href="#i-leaf"/></svg></span><div><b>Konservasi Berkelanjutan</b><p>Pemulihan lahan kritis dipantau secara terorganisir bersama kelompok tani setempat.</p></div></li>
                <li><span><svg class="ic" aria-hidden="true"><use href="#i-shield"/></svg></span><div><b>Data Bersumber Resmi</b><p>Data lahan kritis bersumber dari Peta Lahan Kritis KLHK melalui layanan Satu Peta BIG, dengan BPDAS Ketahun sebagai instansi pembina.</p></div></li>
                <li><span><svg class="ic" aria-hidden="true"><use href="#i-eye"/></svg></span><div><b>Keterbukaan Publik</b><p>Masyarakat dapat melihat realisasi kegiatan rehabilitasi secara transparan kapan saja.</p></div></li>
            </ul>
        </div>
    </div>
</section>

{{-- ===================================================== PETA --}}
<section class="lp-sec lp-peta" id="peta">
    <div class="lp-blob b2" aria-hidden="true"></div>
    <div class="lp-wrap">
        <div class="lp-sec-head">
            <div>
                <span class="lp-eyebrow">Peta Interaktif</span>
                <h2>Jelajahi Wilayah Lahan Kritis</h2>
            </div>
            <p>Cari kecamatan atau kegiatan, saring berdasarkan tingkat kekritisan dan status, lalu klik area pada peta untuk melihat luas, kondisi, dan kegiatan di petak tersebut.</p>
        </div>

        <div class="card map-card">
            @include('partials.map-filter', ['kritisFilter' => $kritis_opts])

            <div class="map-grid">
                <div class="map-box"><div id="map" data-mode="public" data-geojson="{{ route('lahan.geojson') }}"></div></div>
                <div class="info-col">
                    <div class="info-card">
                        <h3 id="kec-nama">Belum ada yang dipilih</h3>
                        <div class="eyebrow" id="kec-sub">KECAMATAN</div>
                        <p class="desc" id="kec-desc">Pilih area pada peta untuk melihat detail.</p>
                        <div class="kv-list">
                            <div class="kv"><span>Tingkat Kekritisan:</span><span class="badge" id="kec-badge">-</span></div>
                            <div class="kv"><span>Luas Lahan Kritis:</span><b class="mono" id="kec-luas">-</b></div>
                            <div class="kv"><span>Instansi Pembina:</span><b id="kec-bpdas">-</b></div>
                            <div class="kv"><span>Program Publik:</span><b class="mono" id="kec-jml">-</b></div>
                        </div>
                    </div>
                    <div class="info-card" style="padding:16px">
                        <h3 style="font-size:12px" id="kec-kegiatan-title">Kegiatan Rehabilitasi Terbuka</h3>
                        <div class="mini-list" id="kec-kegiatan"></div>
                    </div>
                    @include('partials.map-result-list')
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================================================== KEGIATAN --}}
<section class="lp-sec lp-kegiatan" id="kegiatan">
    <div class="lp-wrap">
        <div class="lp-sec-head">
            <div>
                <span class="lp-eyebrow">Informasi Transparansi</span>
                <h2>Program &amp; Kegiatan Rehabilitasi</h2>
            </div>
            <div class="lp-sec-side">
                <p>Daftar mengikuti pencarian dan filter pada peta di atas.</p>
                <div class="lp-carousel-btns">
                    <button type="button" class="lp-round-btn" data-carousel="prev" aria-label="Sebelumnya"><svg class="ic" aria-hidden="true"><use href="#i-arrowleft"/></svg></button>
                    <button type="button" class="lp-round-btn" data-carousel="next" aria-label="Berikutnya"><svg class="ic" aria-hidden="true"><use href="#i-arrowright"/></svg></button>
                </div>
            </div>
        </div>
    </div>

    <div class="lp-track" id="lp-track" tabindex="0" aria-label="Daftar kegiatan rehabilitasi">
        @foreach($publik as $k)
        <article class="card k-card lp-kcard" data-lahan="{{ $k['id_lahan'] }}" data-kec="{{ $k['kec_slug'] }}" data-kecname="{{ $k['kecamatan'] }}" data-status="{{ $k['status'] }}" data-q="{{ $k['q'] }}">
            <div class="lp-kcover cv-{{ $loop->index % 4 }}">
                <span class="badge b-{{ $k['status_class'] }}">{{ $k['status'] }}</span>
                <svg class="ic lp-kcover-ic" aria-hidden="true"><use href="#i-sprout"/></svg>
                <span class="lp-kcover-jenis">{{ $k['jenis'] }}</span>
            </div>
            <div class="lp-kbody">
                <span class="lp-kloc"><svg class="ic" aria-hidden="true"><use href="#i-pin"/></svg> {{ $k['kecamatan'] }} · Petak #{{ $k['id_lahan'] }}</span>
                <h3>{{ $k['nama'] }}</h3>
                <p>Dilaksanakan oleh <b>{{ $k['pj'] }}</b>. Target penanganan <b>{{ $k['target_fmt'] }} Ha</b>, dimulai <b>{{ $k['mulai'] }}</b>@if($k['selesai_aktual']) dan selesai <b>{{ $k['selesai_aktual'] }}</b>@endif.</p>
                <div class="pg">
                    <div class="pg-top"><span>Capaian Progres</span><b>{{ $k['progres'] }}%</b></div>
                    <div class="progress"><i style="width:{{ $k['progres'] }}%"></i></div>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <div class="lp-wrap">
        @if($publik->isEmpty())
        <div class="lp-empty">Belum ada kegiatan rehabilitasi yang dipublikasikan.</div>
        @endif
        <div class="lp-empty" id="k-empty" style="display:none">Tidak ada program yang cocok dengan filter.</div>
    </div>
</section>

{{-- ===================================================== TANYA ASISTEN --}}
<section class="lp-wrap lp-cta-sec">
    <div class="lp-cta-card">
        <div>
            <span class="lp-script">Ada yang ingin ditanyakan?</span>
            <h2>Tanya Asisten LahanKritis</h2>
            <p>Pelajari cara membaca peta, arti tingkat kekritisan, dan program rehabilitasi yang sedang berjalan.</p>
        </div>
        <button type="button" class="lp-btn lp-btn-sun lp-btn-lg" data-open-chatbot>
            <svg viewBox="0 0 24 24" class="ic" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg> Mulai Bertanya
        </button>
    </div>
</section>
@endsection

@push('scripts')
<script>window.APP = @json($app);</script>
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('js/map.js') }}"></script>
@endpush
