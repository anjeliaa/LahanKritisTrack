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
<header class="int-header">
    <div class="wrap">
        <a href="{{ route($tabs[0]['route']) }}" class="logo sm">
            <span class="logo-mark"><span>L<svg class="ic"><use href="#i-leaf"/></svg></span></span>
            <span>
                <span class="logo-name" style="display:block">LahanKritis<b>Track</b></span>
                <span class="logo-sub" style="display:block">Internal System</span>
            </span>
        </a>
        <div class="int-right">
            <button type="button" class="icon-btn" data-theme-toggle aria-label="Ganti tema"><svg class="ic"><use href="#i-moon"/></svg></button>
            <span class="role-pill">{{ $role === 'admin' ? 'Admin' : 'Petugas' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-red-soft" style="padding:6px 12px;border-radius:8px"><svg class="ic"><use href="#i-logout"/></svg> Keluar</button>
            </form>
        </div>
    </div>
</header>

<nav class="int-tabs no-print">
    <div class="wrap">
        @foreach($tabs as $t)
        <a href="{{ route($t['route']) }}" class="{{ $t['key'] === $active ? 'active' : '' }}"><svg class="ic"><use href="#i-{{ $t['icon'] }}"/></svg>{{ $t['label'] }}</a>
        @endforeach
    </div>
</nav>

<main class="int-main">
    <div class="wrap">
        @include('partials.page-header')
        @yield('content')
    </div>
</main>

@yield('modals')
<div id="toast-root"></div>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
