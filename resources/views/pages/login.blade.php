<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — LahanKritisTrack</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="lg">
@include('partials.icons')

<div class="lg-page">
    <div class="lg-card">

        <aside class="lg-art">
            <div class="lg-brand">
                <span class="lg-mark"><span class="lg-mark-letter">L</span><svg class="ic" aria-hidden="true"><use href="#i-leaf"/></svg></span>
                <span><b>LahanKritis<i>Track</i></b><small>Kota Bengkulu</small></span>
            </div>

            <img class="lg-illus" src="{{ asset('img/login-illustration.png') }}" alt="Petugas melakukan rehabilitasi lahan kritis">

            <div class="lg-art-copy">
                <span class="lg-art-tag">Ruang kerja pengelola</span>
                <h2>Pemantauan lahan kritis yang terukur dan terdokumentasi.</h2>
            </div>

            <svg class="lg-curve" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><path d="M100,0 L100,100 L30,100 C62,76 0,62 20,40 C40,20 8,12 40,0 Z"/></svg>
        </aside>

        <main class="lg-form-side">
            <a href="{{ route('home') }}" class="lg-back"><svg class="ic"><use href="#i-arrowleft"/></svg> Kembali ke Landing Page</a>

            <form class="lg-form" method="POST" action="{{ route('login.post') }}">
                @csrf

                <span class="lg-eyebrow">Portal internal</span>
                <h1>Selamat datang kembali</h1>
                <p class="lg-sub">Masuk menggunakan akun pengelola yang telah terdaftar.</p>

                @if(session('error'))
                <div class="lg-alert">{{ session('error') }}</div>
                @endif

                @error('username')
                <div class="lg-alert">{{ $message }}</div>
                @enderror

                <div class="lg-field">
                    <label for="username">Username</label>
                    <div class="lg-input">
                        <svg class="ic"><use href="#i-mail"/></svg>
                        <input id="username" name="username" type="text" placeholder="mis. anjelia.admin" value="{{ old('username') }}" autocomplete="username" autofocus required>
                    </div>
                </div>

                <div class="lg-field">
                    <label for="password">Kata sandi</label>
                    <div class="lg-input">
                        <svg class="ic"><use href="#i-lock"/></svg>
                        <input id="password" name="password" type="password" placeholder="••••••••••••" required>
                        <button type="button" class="lg-eye" data-eye="password" aria-label="Tampilkan kata sandi"><svg class="ic"><use href="#i-eye"/></svg></button>
                    </div>
                </div>

                <div class="lg-row">
                    <label class="lg-check"><input type="checkbox" name="remember" value="1"> Ingat perangkat ini</label>
                    <a href="#" data-toast="Hubungi Admin sistem untuk mengatur ulang kata sandi">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="lg-submit">Masuk ke dashboard <svg class="ic"><use href="#i-arrowright"/></svg></button>

                <div class="lg-note"><svg class="ic"><use href="#i-lock"/></svg> Akses dilindungi dan seluruh aktivitas pengguna dicatat untuk keamanan data.</div>
            </form>

            <div class="lg-foot"><span>&copy; 2026 LahanKritisTrack · Sistem resmi Pemerintah Kota Bengkulu</span><a href="#" data-toast="Hubungi admin sistem untuk bantuan teknis">Butuh bantuan?</a></div>
        </main>
    </div>
</div>

<div id="toast-root"></div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>