@extends('layouts.internal')

@php
    $edit   = $item !== null;
    $judul  = $edit ? 'Ubah Akun Pengguna' : 'Tambah Akun Pengguna';
    $self   = $edit && $item->id_user === auth()->id();
    $rolePilih = old('role', $item->role ?? 'petugas');
    $ketRole = [
        'admin'   => 'Akses penuh: kelola akun, data lahan, kegiatan, monitoring, dan laporan.',
        'petugas' => 'Mencatat kegiatan dan monitoring di lapangan.',
    ];
@endphp

@section('title', $judul . ' — LahanKritisTrack')

@section('page_header')
@include('partials.subpage-header', [
    'icon'  => 'userplus',
    'path'  => 'Manajemen Akun',
    'title' => $judul,
    'desc'  => $edit ? 'Perbarui data akun. Kosongkan kata sandi bila tidak ingin menggantinya.' : 'Buat akun baru untuk Admin atau Petugas lapangan.',
    'back'  => route('admin.akun'),
])
@endsection

@section('content')
<form method="POST" action="{{ $edit ? route('admin.akun.update', $item->id_user) : route('admin.akun.store') }}" class="card form-card" id="form-akun" autocomplete="off">
    @csrf
    @if($edit) @method('PUT') @endif

    @if($errors->any())
    <div class="form-alert">Ada {{ $errors->count() }} isian yang perlu diperbaiki. Periksa kolom yang bertanda merah.</div>
    @endif

    {{-- 1. Identitas --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Identitas</h2>
            <p>Nama lengkap dan username untuk masuk ke sistem.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="nama">Nama Lengkap</label>
                <input id="nama" class="input @error('nama') err @enderror" name="nama" value="{{ old('nama', $item->nama ?? '') }}" placeholder="mis. Budi Santoso" maxlength="255" required>
                @error('nama')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field span2">
                <label for="username">Username</label>
                <input id="username" class="input @error('username') err @enderror" name="username" value="{{ old('username', $item->username ?? '') }}" placeholder="budi.petugas" maxlength="100" required autocomplete="off">
                <div class="hint">Huruf, angka, titik, garis bawah, dan strip.</div>
                @error('username')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 2. Hak akses --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Hak Akses</h2>
            <p>Role menentukan menu dan tindakan yang boleh dilakukan pengguna.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <div class="opt-grid cols2 tight">
                    @foreach(config('lahan.role') as $val => $label)
                    @php $aktif = ! $self || $val === 'admin'; @endphp
                    <label class="opt {{ $aktif ? '' : 'off' }}">
                        <input type="radio" name="role" value="{{ $val }}" @checked($rolePilih === $val) @disabled(! $aktif)>
                        <span class="opt-body">
                            <b>{{ $label }}</b>
                            <small>{{ $ketRole[$val] ?? '' }}</small>
                        </span>
                    </label>
                    @endforeach
                </div>
                @if($self)<div class="hint">Role akun Anda sendiri tidak dapat diturunkan.</div>@endif
                @error('role')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 3. Kata sandi --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Kata Sandi</h2>
            <p>{{ $edit ? 'Isi hanya jika ingin mengganti kata sandi.' : 'Wajib diisi saat membuat akun baru.' }}</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="password">Kata Sandi {{ $edit ? 'Baru' : '' }}</label>
                <div class="pw-wrap">
                    <input id="password" class="input @error('password') err @enderror" name="password" type="password" minlength="8" maxlength="100" placeholder="Minimal 8 karakter" autocomplete="new-password" @unless($edit) required @endunless>
                    <button type="button" class="pw-eye" data-eye="password" aria-label="Tampilkan kata sandi"><svg class="ic"><use href="#i-eye"/></svg></button>
                </div>
                @error('password')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <div class="form-foot">
        <a href="{{ route('admin.akun') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary"><svg class="ic" aria-hidden="true"><use href="#i-save"/></svg> {{ $edit ? 'Simpan Perubahan' : 'Simpan Akun' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('form-akun').addEventListener('submit', function (e) {
    var b = e.target.querySelector('button[type=submit]');
    if (b) setTimeout(function () { b.disabled = true; }, 0);
});
</script>
@endpush