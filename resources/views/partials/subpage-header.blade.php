{{-- Header untuk halaman form/konfirmasi. Parameter: $icon, $path, $title, $desc, $back (URL) --}}
<section class="page-header">
    <div class="ph-left">
        <div class="ph-icon"><svg class="ic" aria-hidden="true"><use href="#i-{{ $icon }}"/></svg></div>
        <div>
            <div class="ph-path">{{ $path }}</div>
            <h1 class="ph-title">{{ $title }}</h1>
            <p class="ph-desc">{{ $desc }}</p>
        </div>
    </div>
    <a href="{{ $back }}" class="btn btn-outline"><svg class="ic" aria-hidden="true"><use href="#i-arrowleft"/></svg> Kembali ke daftar</a>
</section>