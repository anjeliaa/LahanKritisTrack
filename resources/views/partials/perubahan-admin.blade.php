{{-- Pemberitahuan perubahan dari Admin: hanya untuk Petugas, pada halaman Kegiatan dan Monitoring. --}}
@if($role === 'petugas' && in_array($active, ['kegiatan', 'monitoring'], true))
@php
    $baru = $notifBaru ?? collect();
    $lama = auth()->user()->readNotifications()->latest()->limit(5)->get();
    $waktu = fn ($n) => $n->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i');
    $baris = fn ($p) => ($p['lama'] === null && $p['baru'] === null)
        ? $p['label'].' diganti'
        : $p['label'].': '.$p['lama'].' → '.$p['baru'];
@endphp

@if($baru->isNotEmpty())
<section class="pa no-print" aria-label="Perubahan dari Admin">
    <div class="pa-head">
        <h3><svg class="ic" aria-hidden="true"><use href="#i-pen"/></svg>Perubahan dari Admin ({{ $baru->count() }} baru)</h3>
        <form method="POST" action="{{ route('petugas.perubahan.baca') }}">
            @csrf
            <button type="submit" class="btn btn-primary"><svg class="ic" aria-hidden="true"><use href="#i-checkc"/></svg>Tandai sudah dibaca</button>
        </form>
    </div>
    <ul class="pa-list">
        @foreach($baru as $n)
        <li class="pa-item">
            <b>{{ $n->data['kegiatan'] ?? '-' }}</b>
            <span class="pa-meta">{{ ($n->data['jenis'] ?? '') === 'monitoring' ? 'Laporan monitoring' : 'Data kegiatan' }} diubah oleh {{ $n->data['oleh'] ?? 'Admin' }} · {{ $waktu($n) }}</span>
            <ul class="pa-lines">
                @foreach(($n->data['perubahan'] ?? []) as $p)
                <li>{{ $baris($p) }}</li>
                @endforeach
            </ul>
        </li>
        @endforeach
    </ul>
</section>
@endif

@if($lama->isNotEmpty())
<section class="pa pa-lama no-print" aria-label="Riwayat perubahan dari Admin">
    <details>
        <summary><svg class="ic" aria-hidden="true"><use href="#i-chevron"/></svg>Riwayat perubahan dari Admin ({{ $lama->count() }} terakhir)</summary>
        <ul class="pa-list">
            @foreach($lama as $n)
            <li class="pa-item">
                <b>{{ $n->data['kegiatan'] ?? '-' }}</b>
                <span class="pa-meta">{{ ($n->data['jenis'] ?? '') === 'monitoring' ? 'Laporan monitoring' : 'Data kegiatan' }} diubah oleh {{ $n->data['oleh'] ?? 'Admin' }} · {{ $waktu($n) }}</span>
                <ul class="pa-lines">
                    @foreach(($n->data['perubahan'] ?? []) as $p)
                    <li>{{ $baris($p) }}</li>
                    @endforeach
                </ul>
            </li>
            @endforeach
        </ul>
    </details>
</section>
@endif
@endif
