@extends('layouts.internal')

@php
    $edit      = $item !== null;
    $judul     = $edit ? 'Ubah Laporan Monitoring' : 'Catat Laporan Monitoring';
    $listRoute = $role === 'admin' ? 'admin.monitoring' : 'petugas.monitoring';
    $tgl       = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d') : '';

    $kegPilih  = old('id_kegiatan', $item->id_kegiatan ?? request('kegiatan'));
    $tglPilih  = old('tanggal_monitoring', $edit ? $tgl($item->tanggal_monitoring) : now()->toDateString());
    $progPilih = (int) old('progres_persen', $item->progres_persen ?? 50);
    $kondPilih = old('kondisi_lahan', $item->kondisi_lahan ?? collect($kondisi_opts)->first());
    $fotoLama  = ($edit && $item->foto_dokumentasi)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->foto_dokumentasi)
        : null;
@endphp

@section('title', $judul . ' — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => 'camera',
    'path'  => 'Monitoring Lapangan',
    'title' => $judul,
    'desc'  => $edit ? 'Perbarui laporan monitoring, lalu simpan perubahan.' : 'Catat progres dan kondisi lahan hasil pantauan di lapangan.',
    'back'  => route($listRoute),
])
@endsection

@section('content')

@if(collect($kegiatan_opsi)->isEmpty())
<div class="card form-card">
    <div class="empty" style="padding:40px 24px">
        Belum ada kegiatan yang bisa dipantau.
        @if($role === 'petugas')
        Tambahkan kegiatan terlebih dahulu atau minta Admin menugaskannya kepada Anda.
        <div style="margin-top:16px"><a href="{{ route('kegiatan.create') }}" class="btn btn-primary">Tambah Kegiatan</a></div>
        @endif
    </div>
</div>
@else
<form method="POST" action="{{ $edit ? route('monitoring.update', $item) : route('monitoring.store') }}" enctype="multipart/form-data" class="card form-card" id="form-monitoring">
    @csrf
    @if($edit) @method('PUT') @endif

    @if($errors->any())
    <div class="form-alert">Ada {{ $errors->count() }} isian yang perlu diperbaiki. Periksa kolom yang bertanda merah.</div>
    @endif

    {{-- 1. Kegiatan & waktu --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Kegiatan &amp; Waktu</h2>
            <p>Pilih kegiatan yang dipantau dan tanggal pemantauan.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="id_kegiatan">Kegiatan Terkait</label>
                <select id="id_kegiatan" class="select @error('id_kegiatan') err @enderror" name="id_kegiatan" required>
                    @foreach($kegiatan_opsi as $k)
                    <option value="{{ $k['id'] }}" @selected((string) $kegPilih === (string) $k['id'])>{{ $k['nama'] }} — {{ $k['kecamatan'] }}</option>
                    @endforeach
                </select>
                @error('id_kegiatan')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="tanggal_monitoring">Tanggal Monitoring</label>
                <input id="tanggal_monitoring" class="input @error('tanggal_monitoring') err @enderror" name="tanggal_monitoring" type="date" value="{{ $tglPilih }}" max="{{ now()->toDateString() }}">
                @error('tanggal_monitoring')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 2. Hasil lapangan --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Hasil Lapangan</h2>
            <p>Progres fisik dan kondisi lahan saat dipantau. Status kegiatan menyesuaikan otomatis dengan progres.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="progres_persen">Progres Fisik Lapangan</label>
                <div class="range-row">
                    <input id="progres_persen" type="range" name="progres_persen" min="0" max="100" value="{{ $progPilih }}">
                    <span class="range-val" id="rv-progres">{{ $progPilih }}%</span>
                </div>
                @error('progres_persen')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field span2">
                <label for="kondisi_lahan">Kondisi Lahan</label>
                <select id="kondisi_lahan" class="select @error('kondisi_lahan') err @enderror" name="kondisi_lahan">
                    @foreach($kondisi_opts as $o)
                    <option value="{{ $o }}" @selected($kondPilih === $o)>{{ $o }}</option>
                    @endforeach
                </select>
                @error('kondisi_lahan')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 3. Dokumentasi --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Dokumentasi</h2>
            <p>Foto lapangan dan catatan hasil pemantauan.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="foto_dokumentasi">Foto Dokumentasi</label>

                <div class="foto-box" id="foto-box" @if(!$fotoLama) hidden @endif>
                    <img id="foto-img" src="{{ $fotoLama }}" alt="Foto dokumentasi">
                    <small id="foto-cap">{{ $fotoLama ? 'Foto saat ini. Pilih berkas baru untuk menggantinya.' : '' }}</small>
                </div>

                <input id="foto_dokumentasi" class="input @error('foto_dokumentasi') err @enderror" name="foto_dokumentasi" type="file" accept="image/jpeg,image/png,image/webp">
                <div class="hint">JPG / PNG / WEBP, maks. 4 MB.@if($edit) Kosongkan untuk mempertahankan foto yang ada.@endif</div>
                @error('foto_dokumentasi')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field span2">
                <label for="catatan">Catatan Monitoring Lapangan</label>
                <textarea id="catatan" class="input @error('catatan') err @enderror" name="catatan" placeholder="Catatan hasil monitoring..." maxlength="2000">{{ old('catatan', $item->catatan ?? '') }}</textarea>
                @error('catatan')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <div class="form-foot">
        <a href="{{ route($listRoute) }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary"><svg class="ic" aria-hidden="true"><use href="#i-save"/></svg> {{ $edit ? 'Simpan Perubahan' : 'Simpan Laporan' }}</button>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var f = document.getElementById('form-monitoring');
    if (!f) return;

    // tampilan nilai slider
    var range = document.getElementById('progres_persen');
    var val = document.getElementById('rv-progres');
    range.addEventListener('input', function () { val.textContent = range.value + '%'; });

    // pratinjau foto yang baru dipilih
    var file = document.getElementById('foto_dokumentasi');
    var box = document.getElementById('foto-box');
    var img = document.getElementById('foto-img');
    var cap = document.getElementById('foto-cap');
    file.addEventListener('change', function () {
        var fl = file.files && file.files[0];
        if (!fl) return;
        var rd = new FileReader();
        rd.onload = function (ev) {
            img.src = ev.target.result;
            cap.textContent = 'Foto baru: ' + fl.name;
            box.hidden = false;
        };
        rd.readAsDataURL(fl);
    });

    // cegah klik ganda saat menyimpan
    f.addEventListener('submit', function () {
        var b = f.querySelector('button[type=submit]');
        if (b) setTimeout(function () { b.disabled = true; }, 0);
    });
})();
</script>
@endpush