{{-- Notifikasi perubahan dari Admin: ikon lonceng di pojok atas, hanya untuk Petugas. --}}
@if($role === 'petugas')
@php
    $baru = $notifBaru ?? collect();
    $lama = auth()->user()->readNotifications()->latest()->limit(5)->get();
    $waktu = fn ($n) => $n->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i');
    $baris = fn ($p) => ($p['lama'] === null && $p['baru'] === null)
        ? $p['label'].' diganti'
        : $p['label'].': '.$p['lama'].' → '.$p['baru'];
    $butir = function ($n, $belum) use ($waktu, $baris) {
        $judul = ($n->data['jenis'] ?? '') === 'monitoring' ? 'Laporan monitoring' : 'Data kegiatan';
        $h = '<li class="nf-item'.($belum ? ' nf-new' : '').'"><b>'.e($n->data['kegiatan'] ?? '-').'</b>'
           .'<span class="nf-meta">'.e($judul).' diubah oleh '.e($n->data['oleh'] ?? 'Admin').' · '.e($waktu($n)).'</span><ul class="nf-lines">';
        foreach (($n->data['perubahan'] ?? []) as $p) { $h .= '<li>'.e($baris($p)).'</li>'; }
        return new \Illuminate\Support\HtmlString($h.'</ul></li>');
    };
@endphp
<div class="nf no-print" data-nf>
    <button type="button" class="nf-btn" data-nf-toggle aria-label="Notifikasi" aria-expanded="false" title="Notifikasi">
        <svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg>
        @if($baru->isNotEmpty())<span class="nf-dot" data-lencana>{{ $baru->count() }}</span>@endif
    </button>
    <div class="nf-pop" data-nf-pop hidden>
        <div class="nf-head">
            <h3>Notifikasi</h3>
            @if($baru->isNotEmpty())
            <form method="POST" action="{{ route('petugas.perubahan.baca') }}">
                @csrf
                <button type="submit" class="nf-read">Tandai sudah dibaca</button>
            </form>
            @endif
        </div>
        <div class="nf-body">
            @if($baru->isEmpty() && $lama->isEmpty())
                <p class="nf-empty">Belum ada perubahan dari Admin.</p>
            @endif
            @if($baru->isNotEmpty())
                <div class="nf-sec">Baru ({{ $baru->count() }})</div>
                <ul class="nf-list">@foreach($baru as $n){{ $butir($n, true) }}@endforeach</ul>
            @endif
            @if($lama->isNotEmpty())
                <div class="nf-sec">Sebelumnya</div>
                <ul class="nf-list">@foreach($lama as $n){{ $butir($n, false) }}@endforeach</ul>
            @endif
        </div>
    </div>
</div>
@endif
