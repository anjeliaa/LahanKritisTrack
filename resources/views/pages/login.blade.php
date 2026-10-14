<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — LahanKritisTrack</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@include('partials.icons')
<div class="login-page">
    <aside class="login-left">
        <div class="brand">
            <span class="bm"><svg class="ic"><use href="#i-leaf"/></svg></span>
            <span><b>LahanKritisTrack</b><small>Kota Bengkulu</small></span>
        </div>
        <div class="mid">
            <div class="tag">Ruang kerja pengelola</div>
            <h2>Data lapangan yang tertata, keputusan yang lebih tepat.</h2>
            <p>Kelola pemantauan, verifikasi progres, dan laporan rehabilitasi lahan kritis Kota Bengkulu dalam satu ruang terpadu.</p>
            <div class="login-visual">
                <svg class="mapsvg" viewBox="0 0 520 270" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <rect width="520" height="270" fill="#dcefe0"/>
                    <polygon points="70,70 150,40 205,95 190,170 105,180 60,125" fill="#e97b3b"/>
                    <polygon points="190,120 290,100 340,160 300,235 215,240 170,190" fill="#a8d65c"/>
                    <path d="M20,250 C120,215 150,150 240,120 S360,70 410,10" stroke="#7fb2e0" stroke-width="9" fill="none" stroke-linecap="round" opacity=".9"/>
                </svg>
                <div class="lv-tag"><b>Wilayah rehabilitasi aktif</b><small>KOTA BENGKULU · 2026</small></div>
                <div class="lv-card">
                    <div class="t"><span>Progres rehabilitasi</span><svg class="ic"><use href="#i-trendup"/></svg></div>
                    <div class="v">645.0 Ha</div>
                    <div class="bar"><i></i></div>
                    <div class="f"><span>Target tahunan</span><span class="mono">78.4%</span></div>
                </div>
            </div>
        </div>
        <div class="foot"><svg class="ic"><use href="#i-shield"/></svg> Sistem resmi Pemerintah Kota Bengkulu · Terintegrasi BPDAS Ketahun</div>
    </aside>

    <main class="login-right">
        <a href="{{ route('home') }}" class="login-back"><svg class="ic"><use href="#i-arrowleft"/></svg> Kembali ke Landing Page</a>
        <form class="login-form" method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="tag">Portal internal</div>
            <h1>Selamat datang kembali</h1>
            <p class="sub">Masuk menggunakan akun pengelola yang telah terdaftar.</p>

            @if(session('error'))
            <div class="alert">{{ session('error') }}</div>
            @endif

            <div class="field">
                <label for="email">Alamat email</label>
                <div class="iconfield"><svg class="ic"><use href="#i-mail"/></svg><input id="email" name="email" type="email" class="input" placeholder="admin@lahankritis.bengkulukota.go.id" value="{{ old('email') }}" required></div>
            </div>
            <div class="field">
                <label for="password">Kata sandi</label>
                <div class="iconfield">
                    <svg class="ic"><use href="#i-lock"/></svg>
                    <input id="password" name="password" type="password" class="input" placeholder="••••••••••••" required>
                    <button type="button" class="eye" data-eye="password" aria-label="Tampilkan kata sandi"><svg class="ic"><use href="#i-eye"/></svg></button>
                </div>
            </div>
            <div class="login-row">
                <label><input type="checkbox" name="remember" checked> Ingat perangkat ini</label>
                <a href="#" data-toast="Fitur reset kata sandi akan tersedia setelah backend terhubung">Lupa kata sandi?</a>
            </div>
            <button type="submit" class="login-submit">Masuk ke dashboard <svg class="ic"><use href="#i-arrowright"/></svg></button>
            <div class="login-note"><svg class="ic"><use href="#i-lock"/></svg> Akses dilindungi dan seluruh aktivitas pengguna dicatat untuk keamanan data.</div>

            <div class="login-demo">
                Mode demo (tanpa database) — email berisi “petugas” masuk sebagai Petugas, selain itu sebagai Admin.<br>
                <a href="{{ route('login.demo', ['role' => 'admin']) }}">Masuk cepat: Admin</a>
                <a href="{{ route('login.demo', ['role' => 'petugas']) }}">Masuk cepat: Petugas</a>
            </div>
        </form>
        <div class="login-foot"><span>&copy; 2026 LahanKritisTrack</span><a href="#" data-toast="Hubungi admin sistem untuk bantuan teknis">Butuh bantuan teknis?</a></div>
    </main>
</div>
<div id="toast-root"></div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
