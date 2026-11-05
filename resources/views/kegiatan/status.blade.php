@extends('layouts.internal')

@php
    $listRoute = $role === 'admin' ? 'admin.kegiatan' : 'petugas.kegiatan';
    $dipilih   = old('status', $item->status);
    $lokasi    = ($ringkas['kecamatan'] ?? '—') . ' · Petak #' . $item->id_lahan;
    $keterangan = [
        'Rencana'  => 'Belum dimulai di lapangan.',
        'Berjalan' => 'Pekerjaan sedang dilaksanakan.',
        'Selesai'  => 'Seluruh pekerjaan sudah tuntas.',
    ];
@endphp

@section('title', 'Update Status Kegiatan — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => 'refresh',
    'path'  => 'Kegiatan Rehabilitasi',
    'title' => 'Update Status Kegiatan',
    'desc'  => 'Pilih status yang sesuai dengan kondisi kegiatan di lapangan.',
    'back'  => route($listRoute),
])
@endsection

@section('content')
<div class="st-wrap">

    <div class="card st-sum">
        <div>
            <h2>{{ $item->nama_kegiatan }}</h2>
            <div class="st-loc">{{ $lokasi }}</div>
        </div>

        <div class="st-prog">
            <div class="prog-cell">
                <div class="progress" style="width:100%"><i style="width:{{ $progres }}%"></i></div>
                <span>{{ $progres }}%</span>
            </div>
            @if($role === 'petugas')
            <a href="{{ route('monitoring.create', ['kegiatan' => $item->getKey()]) }}" class="btn btn-soft"><svg class="ic" aria-hidden="true"><use href="#i-camera"/></svg> Perbarui progres</a>
            @endif
        </div>
        <div class="st-note">Progres diambil dari catatan monitoring terbaru. Untuk mengubahnya, catat monitoring baru.</div>
    </div>

    <form method="POST" action="{{ route('kegiatan.status') }}" class="card form-card" id="form-status">
        @csrf
        <input type="hidden" name="kegiatan" value="{{ $item->getKey() }}">

        @error('status')<div class="form-alert">{{ $message }}</div>@enderror

        <div class="st-body">
            <div class="form-sec-head" style="margin-bottom:14px">
                <h2>Status Baru</h2>
                <p>Status harus sejalan dengan progres monitoring terbaru. Status Selesai terisi otomatis saat progres mencapai 100%.</p>
            </div>

            <div class="opt-grid">
                @foreach($keterangan as $s => $ket)
                @php $aktif = in_array($s, $boleh, true) || $s === $item->status; @endphp
                <label class="opt {{ $aktif ? '' : 'off' }}">
                    <input type="radio" name="status" value="{{ $s }}" @checked($dipilih === $s) @disabled(! $aktif)>
                    <span class="opt-body">
                        <b>{{ $s }}</b>
                        <small>{{ $ket }}</small>
                        @unless($aktif)<em>Tidak sesuai progres {{ $progres }}%</em>@endunless
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-foot">
            <a href="{{ route($listRoute) }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary"><svg class="ic" aria-hidden="true"><use href="#i-save"/></svg> Simpan Status</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('form-status').addEventListener('submit', function (e) {
    var b = e.target.querySelector('button[type=submit]');
    if (b) setTimeout(function () { b.disabled = true; }, 0);
});
</script>
@endpush