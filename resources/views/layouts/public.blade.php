<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LahanKritisTrack')</title>
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@include('partials.icons')
<header class="pub-header">
    <div class="wrap">
        <a href="{{ route('home') }}" class="logo">
            <span class="logo-mark"><span>L<svg class="ic"><use href="#i-leaf"/></svg></span></span>
            <span>
                <span class="logo-name" style="display:block">LahanKritis<b>Track</b></span>
                <span class="logo-sub" style="display:block">Sistem Informasi Peta Interaktif &amp; Monitoring Program Rehabilitasi Lahan Kritis Kota Bengkulu</span>
            </span>
        </a>
        <nav class="pub-nav">
            <a class="nav-l" href="#top">Beranda</a>
            <a class="nav-l" href="#peta">Peta Lahan Kritis</a>
            <a class="nav-l" href="#kegiatan">Kegiatan Rehabilitasi</a>
            <a class="nav-l" href="#statistik">Statistik Wilayah</a>
            <button type="button" class="icon-btn" data-theme-toggle aria-label="Ganti tema"><svg class="ic"><use href="#i-moon"/></svg></button>
            <a href="{{ route('login') }}" class="btn btn-gradient"><svg class="ic"><use href="#i-login"/></svg> Masuk Sistem</a>
        </nav>
    </div>
</header>

@yield('content')

<footer class="pub-footer">
    <div class="wrap">
        <div class="fl"><i><svg class="ic"><use href="#i-leaf"/></svg></i> LahanKritisTrack Kota Bengkulu</div>
        <div>&copy; 2026 Pemerintah Kota Bengkulu &amp; BPDAS Ketahun Bengkulu. Hak Cipta Dilindungi.</div>
    </div>
</footer>

<div id="toast-root"></div>
<script src="{{ asset('js/app.js') }}"></script>
@if(session('success') || session('error'))
<script>@if(session('success')) toast(@json(session('success'))); @endif @if(session('error')) toast(@json(session('error')), true); @endif</script>
@endif
@stack('scripts')
</body>
</html>
