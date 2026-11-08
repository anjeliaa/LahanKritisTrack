<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LahanKritisTrack')</title>
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}">
</head>
<body class="shell">
@include('partials.icons')
@php
    $u = auth()->user();
    $roleLabel = $role === 'admin' ? 'Admin' : 'Petugas';
    $inisial = mb_strtoupper(mb_substr($u->nama, 0, 1));
    // Petugas: perubahan dari Admin yang belum dibaca (lencana di sidebar + panel di halaman)
    $notifBaru = $role === 'petugas' ? $u->unreadNotifications()->get() : collect();
    $lencana = [
        'kegiatan'   => $notifBaru->filter(fn ($n) => ($n->data['jenis'] ?? null) === 'kegiatan')->count(),
        'monitoring' => $notifBaru->filter(fn ($n) => ($n->data['jenis'] ?? null) === 'monitoring')->count(),
    ];
@endphp

<aside class="sb no-print" aria-label="Navigasi utama">
    <a href="{{ route($tabs[0]['route']) }}" class="sb-brand">
        <span class="sb-mark"><b>L</b><svg class="ic" aria-hidden="true"><use href="#i-leaf"/></svg></span>
        <span>
            <span class="sb-name">LahanKritis<i>Track</i></span>
            <span class="sb-sub">Internal System</span>
        </span>
    </a>

    <div class="sb-label">Menu</div>
    <nav class="sb-nav">
        @foreach($tabs as $t)
        <a href="{{ route($t['route']) }}" class="{{ $t['key'] === $active ? 'active' : '' }}">
            <svg class="ic" aria-hidden="true"><use href="#i-{{ $t['icon'] }}"/></svg>{{ $t['label'] }}
            @if(($lencana[$t['key']] ?? 0) > 0)<span class="sb-badge" data-lencana title="Perubahan dari Admin">{{ $lencana[$t['key']] }}</span>@endif
        </a>
        @endforeach
    </nav>

    <div class="sb-foot">
        <a href="{{ route('profil') }}" class="sb-user {{ $active === 'profil' ? 'active' : '' }}" title="Pengaturan akun">
            <span class="sb-av" aria-hidden="true">{{ $inisial }}</span>
            <span><b>{{ $u->nama }}</b><small>{{ $roleLabel }} · Pengaturan akun</small></span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-out"><svg class="ic" aria-hidden="true"><use href="#i-logout"/></svg> Keluar</button>
        </form>
    </div>
</aside>
<div class="sb-backdrop" data-sb-toggle></div>

<div class="sb-main">
    <main class="int-main">
        <div class="wrap">

            {{-- tombol menu (HP), tanggal & role: melayang di pojok pita hijau --}}
            <div class="hb-util no-print">
                <button type="button" class="hb-burger" data-sb-toggle aria-label="Buka menu"><svg class="ic"><use href="#i-bars"/></svg></button>
                <div class="hb-right">
                    <span class="hb-pill"><svg class="ic" aria-hidden="true"><use href="#i-cal"/></svg>{{ now('Asia/Jakarta')->locale('id')->translatedFormat('l, j F Y') }}</span>
                    <span class="hb-role">{{ $roleLabel }}</span>
                </div>
            </div>

            @hasSection('page_header')
                @yield('page_header')
            @else
                @include('partials.page-header')
            @endif
            @include('partials.perubahan-admin')
            @yield('content')
        </div>
    </main>
</div>

@yield('modals')
<div id="toast-root"></div>
<script src="{{ asset('js/app.js') }}"></script>
<script>
document.addEventListener('click', function (e) {
    if (e.target.closest('[data-sb-toggle]')) document.body.classList.toggle('sb-open');
    else if (document.body.classList.contains('sb-open') && !e.target.closest('.sb')) document.body.classList.remove('sb-open');
});
</script>
@if(session('success') || session('error'))
<script>@if(session('success')) toast(@json(session('success'))); @endif @if(session('error')) toast(@json(session('error')), true); @endif</script>
@endif
@stack('scripts')
</body>
</html>