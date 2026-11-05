@extends('layouts.internal')

@php
    $listRoute = $role === 'admin' ? 'admin.monitoring' : 'petugas.monitoring';
    $terbaru   = $riwayat[0] ?? ['progres' => 0, 'kondisi' => '-', 'kondisi_class' => ''];
@endphp

@section('title', 'Riwayat Monitoring — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => 'eye',
    'path'  => 'Monitoring Lapangan',
    'title' => 'Riwayat Monitoring',
    'desc'  => 'Seluruh catatan pemantauan lapangan untuk kegiatan ini, dari yang terbaru.',
    'back'  => route($listRoute),
])
@endsection

@section('content')

{{-- Ringkasan kegiatan --}}
<div class="card rw-sum">
    <div class="rw-sum-main">
        <h2>{{ $baris['kegiatan'] }}</h2>
        <div class="rw-sum-loc">{{ $baris['lokasi'] }} · Petak #{{ $baris['id_lahan'] }} · {{ count($riwayat) }} catatan</div>
    </div>
    <div class="rw-sum-prog">
        <div class="prog-cell">
            <div class="progress" style="width:100%"><i style="width:{{ $terbaru['progres'] }}%"></i></div>
            <span>{{ $terbaru['progres'] }}%</span>
        </div>
        <span class="badge b-{{ $terbaru['kondisi_class'] }}">{{ $terbaru['kondisi'] }}</span>
    </div>
</div>

{{-- Daftar catatan --}}
<div class="rw-list">
    @forelse($riwayat as $i => $r)
    <article class="card rw-item">
        <div class="rw-date">
            <b>{{ $r['tanggal'] }}</b>
            @if($i === 0)<span class="rw-new">Terbaru</span>@endif
        </div>

        <div class="rw-main">
            <div class="rw-top">
                <b class="mono">{{ $r['progres'] }}%</b>
                <span class="badge b-{{ $r['kondisi_class'] }}">{{ $r['kondisi'] }}</span>
            </div>
            <div class="progress"><i style="width:{{ $r['progres'] }}%"></i></div>

            @if(!empty($r['catatan']) && $r['catatan'] !== '-')
            <p class="rw-note">{{ $r['catatan'] }}</p>
            @endif

            <div class="rw-meta">Dicatat oleh {{ $r['petugas'] }}</div>

            @if(!empty($r['can_edit']) || !empty($r['can_delete']))
            <div class="acts" style="margin-top:12px">
                @if(!empty($r['can_edit']))
                <a href="{{ $r['url'] }}/ubah" class="act act-ubah"><svg class="ic"><use href="#i-pen"/></svg> Ubah</a>
                @endif
                @if(!empty($r['can_delete']))
                {{-- Hapus tetap popup --}}
                <button type="button" class="act act-hapus" data-open="modal-hapus"
                    data-name="Catatan monitoring {{ $baris['kegiatan'] }} ({{ $r['tanggal'] }})"
                    data-action="{{ $r['url_destroy'] }}" data-method="DELETE">
                    <svg class="ic"><use href="#i-trash"/></svg> Hapus
                </button>
                @endif
            </div>
            @endif
        </div>

        @if(!empty($r['foto']))
        <a class="rw-foto" href="{{ $r['foto'] }}" target="_blank" rel="noopener" title="Buka foto">
            <img src="{{ $r['foto'] }}" alt="Dokumentasi" loading="lazy">
        </a>
        @endif
    </article>
    @empty
    <div class="card"><div class="empty">Belum ada catatan monitoring.</div></div>
    @endforelse
</div>
@endsection

@section('modals')
@include('partials.modals.hapus')
@endsection