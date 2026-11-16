```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0c3a2b">

    <title>@yield('title', 'LahanKritisTrack')</title>

    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">

    <style>
        html {
            scroll-behavior: smooth;
        }

        #visualisasi-temporal {
            scroll-margin-top: 110px;
        }
    </style>
</head>

<body class="lp">

@include('partials.icons')

<a href="#konten" class="lp-skip">
    Lewati ke konten utama
</a>

<header class="lp-header" id="lp-header">
    <div class="lp-bar">

        <a href="{{ route('home') }}" class="lp-logo"
           aria-label="LahanKritisTrack — beranda">

            <span class="lp-logo-mark">
                <span class="lp-logo-letter">L</span>
                <svg class="ic" aria-hidden="true">
                    <use href="#i-leaf"/>
                </svg>
            </span>

            <span class="lp-logo-text">
                <b>LahanKritis<i>Track</i></b>
            </span>
        </a>

        <nav class="lp-nav" id="lp-nav" aria-label="Navigasi utama">

            <a href="{{ route('home') }}#top" data-scroll-section>
                Beranda
            </a>

            <a href="{{ route('home') }}#peta" data-scroll-map>
                Peta Lahan Kritis
            </a>

            <a href="{{ route('home') }}#visualisasi-temporal"
               data-scroll-temporal>
                Visualisasi Temporal
            </a>

            <a href="{{ route('home') }}#kegiatan" data-scroll-section>
                Kegiatan Rehabilitasi
            </a>

        </nav>

        <div class="lp-actions">

            <a href="{{ route('login') }}" class="lp-btn lp-btn-sun lp-login">

                <svg class="ic" aria-hidden="true">
                    <use href="#i-login"/>
                </svg>

                <span>Masuk Sistem</span>
            </a>

            <button type="button"
                    class="lp-icon-btn lp-burger"
                    id="lp-burger"
                    aria-label="Buka menu"
                    aria-expanded="false"
                    aria-controls="lp-nav">

                <svg viewBox="0 0 24 24" class="ic" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>

            </button>

        </div>
    </div>
</header>

<main id="konten">
    @yield('content')
</main>

<footer class="lp-footer">

    <svg class="lp-footer-wave"
         viewBox="0 0 1440 90"
         preserveAspectRatio="none"
         aria-hidden="true">

        <path d="M0,0 L1440,0 L1440,38 C1200,92 1000,6 760,44 C520,82 300,14 0,62 Z"/>

    </svg>

    <div class="lp-footer-in">

        <div class="lp-footer-brand">

            <a href="{{ route('home') }}" class="lp-logo">

                <span class="lp-logo-mark">
                    <span class="lp-logo-letter">L</span>

                    <svg class="ic" aria-hidden="true">
                        <use href="#i-leaf"/>
                    </svg>
                </span>

                <span class="lp-logo-text">
                    <b>LahanKritis<i>Track</i></b>
                </span>

            </a>

            <p>
                Portal informasi terbuka tentang sebaran lahan kritis
                dan kemajuan program rehabilitasi di Kota Bengkulu.
            </p>

        </div>

        <div>

            <h4>Jelajahi</h4>

            <ul>
                <li>
                    <a href="{{ route('home') }}#top" data-scroll-section>
                        Beranda
                    </a>
                </li>

                <li>
                    <a href="{{ route('home') }}#peta" data-scroll-map>
                        Peta Lahan Kritis
                    </a>
                </li>

                <li>
                    <a href="{{ route('home') }}#visualisasi-temporal"
                       data-scroll-temporal>
                        Visualisasi Temporal
                    </a>
                </li>

                <li>
                    <a href="{{ route('home') }}#kegiatan" data-scroll-section>
                        Kegiatan Rehabilitasi
                    </a>
                </li>

                <li>
                    <a href="{{ route('login') }}">
                        Masuk Sistem
                    </a>
                </li>
            </ul>

        </div>

        <div>

            <h4>Media Sosial</h4>

            <div class="lp-social">

                <a href="https://instagram.com/akun-anda"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Instagram"
                   title="Instagram">

                    <svg class="ic" viewBox="0 0 24 24">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
                    </svg>

                </a>

                <a href="https://facebook.com/akun-anda"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Facebook"
                   title="Facebook">

                    <svg class="ic" viewBox="0 0 24 24">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>

                </a>

                <a href="https://youtube.com/@akun-anda"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="YouTube"
                   title="YouTube">

                    <svg class="ic" viewBox="0 0 24 24">
                        <path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/>
                        <path d="m10 15 5-3-5-3z"/>
                    </svg>

                </a>

            </div>

        </div>

    </div>

    <div class="lp-footer-bottom">
        &copy; 2026 Hak Cipta Dilindungi.
    </div>

</footer>

@include('partials.chatbot')

<div id="toast-root"></div>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/chatbot.js') }}"></script>
<script src="{{ asset('js/landing.js') }}"></script>

@if(session('success') || session('error'))
<script>
    @if(session('success'))
        toast(@json(session('success')));
    @endif

    @if(session('error'))
        toast(@json(session('error')), true);
    @endif
</script>
@endif

@stack('scripts')

<script>
(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Smooth Scroll Navigasi
    |--------------------------------------------------------------------------
    */

    function scrollToTarget(target) {
        if (!target) {
            return;
        }

        var header = document.getElementById('lp-header');

        var headerHeight = header
            ? header.offsetHeight
            : 0;

        var targetPosition =
            target.getBoundingClientRect().top
            + window.scrollY
            - headerHeight
            - 20;

        window.scrollTo({
            top: Math.max(0, targetPosition),
            behavior: 'smooth'
        });
    }

    function closeMobileMenu() {
        var nav = document.getElementById('lp-nav');
        var burger = document.getElementById('lp-burger');

        if (nav && burger) {
            nav.classList.remove('open');
            burger.setAttribute('aria-expanded', 'false');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Navigasi Temporal
    |--------------------------------------------------------------------------
    */

    var temporalLinks = document.querySelectorAll(
        'a[data-scroll-temporal]'
    );

    temporalLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = document.getElementById(
                'visualisasi-temporal'
            );

            /*
             * Jika sedang berada di halaman beranda,
             * lakukan scroll langsung.
             *
             * Jika berada di halaman lain, biarkan browser
             * membuka beranda melalui href link.
             */

            var homeUrl = new URL(
                @json(route('home')),
                window.location.origin
            );

            var currentUrl = new URL(window.location.href);

            var isHomePage =
                currentUrl.pathname === homeUrl.pathname;

            if (!isHomePage || !target) {
                return;
            }

            event.preventDefault();

            scrollToTarget(target);

            history.replaceState(
                null,
                '',
                '#visualisasi-temporal'
            );

            closeMobileMenu();

            setActive(link);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Navigasi Section Lain
    |--------------------------------------------------------------------------
    */

    var sectionLinks = document.querySelectorAll(
        'a[data-scroll-section]'
    );

    sectionLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var href = link.getAttribute('href');

            if (!href) {
                return;
            }

            var targetId = href.split('#')[1];

            var target = targetId
                ? document.getElementById(targetId)
                : null;

            var homeUrl = new URL(
                @json(route('home')),
                window.location.origin
            );

            var currentUrl = new URL(window.location.href);

            var isHomePage =
                currentUrl.pathname === homeUrl.pathname;

            if (!isHomePage || !target) {
                return;
            }

            event.preventDefault();

            scrollToTarget(target);

            history.replaceState(
                null,
                '',
                '#' + targetId
            );

            closeMobileMenu();

            setActive(link);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Navigasi Peta
    |--------------------------------------------------------------------------
    */

    var mapLinks = document.querySelectorAll(
        'a[data-scroll-map]'
    );

    mapLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = document.getElementById('peta');

            var homeUrl = new URL(
                @json(route('home')),
                window.location.origin
            );

            var currentUrl = new URL(window.location.href);

            var isHomePage =
                currentUrl.pathname === homeUrl.pathname;

            if (!isHomePage || !target) {
                return;
            }

            event.preventDefault();

            scrollToTarget(target);

            history.replaceState(
                null,
                '',
                '#peta'
            );

            closeMobileMenu();

            setActive(link);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Navigasi Aktif Berdasarkan Scroll
    |--------------------------------------------------------------------------
    */

    var nav = document.getElementById('lp-nav');

    var navLinks = nav
        ? nav.querySelectorAll('a')
        : [];

    var sections = [];

    navLinks.forEach(function (link) {
        var href = link.getAttribute('href');

        if (!href || href.indexOf('#') === -1) {
            return;
        }

        var id = href.split('#')[1];

        var element = id
            ? document.getElementById(id)
            : null;

        if (element) {
            sections.push({
                link: link,
                element: element
            });
        }
    });

    function setActive(link) {
        navLinks.forEach(function (item) {
            item.classList.remove('active');
        });

        if (link) {
            link.classList.add('active');
        }
    }

    function updateActiveNavigation() {
        var position = window.scrollY + 150;

        var current = null;

        sections.forEach(function (section) {
            if (section.element.offsetTop <= position) {
                current = section.link;
            }
        });

        setActive(current);
    }

    window.addEventListener(
        'scroll',
        updateActiveNavigation,
        { passive: true }
    );

    updateActiveNavigation();

})();
</script>

</body>
</html>
```