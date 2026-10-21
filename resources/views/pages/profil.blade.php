@extends('layouts.internal')
@section('title', 'Pengaturan Akun — LahanKritisTrack')

@section('content')
<div class="profile-grid">

    {{-- lihatProfil() --}}
    <section class="card">
        <div class="card-head"><h2>Informasi Akun</h2></div>
        <div class="profile-body">
            <div class="profile-id">
                <div class="profile-avatar" aria-hidden="true">{{ $akun['inisial'] }}</div>
                <div>
                    <h3>{{ $akun['nama'] }}</h3>
                    <div class="mono muted">{{ '@'.$akun['username'] }}</div>
                    <span class="badge b-{{ $role }}" style="margin-top:6px;display:inline-block">{{ $akun['role'] }}</span>
                </div>
            </div>
            <div class="kv-list">
                <div class="kv"><span>Nama Lengkap</span><b>{{ $akun['nama'] }}</b></div>
                <div class="kv"><span>Username</span><b class="mono">{{ $akun['username'] }}</b></div>
                <div class="kv"><span>Role Akses</span><b>{{ $akun['role'] }}</b></div>
                <div class="kv"><span>Akun Dibuat</span><b>{{ $akun['dibuat'] }}</b></div>
                @if($akun['jml_kegiatan'] !== null)
                <div class="kv"><span>Kegiatan Dikelola</span><b class="mono">{{ $akun['jml_kegiatan'] }} Program</b></div>
                @endif
            </div>
            <p class="hint">Nama, username, dan role hanya dapat diubah oleh Admin melalui menu Manajemen Akun Pengguna.</p>
        </div>
    </section>

    {{-- ubahPassword() --}}
    <section class="card">
        <div class="card-head"><h2>Ubah Kata Sandi</h2></div>
        <form class="profile-body" method="POST" action="{{ route('profil.password') }}" data-ajax autocomplete="off">
            @csrf
            @method('PUT')
            <div class="field"><label for="password_lama">Kata Sandi Saat Ini</label>
                <input class="input" id="password_lama" name="password_lama" type="password" autocomplete="current-password" required></div>
            <div class="field"><label for="password_baru">Kata Sandi Baru</label>
                <input class="input" id="password_baru" name="password_baru" type="password" minlength="8" maxlength="100" autocomplete="new-password" required>
                <div class="hint">Minimal 8 karakter dan berbeda dari kata sandi saat ini.</div></div>
            <div class="field"><label for="password_baru_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input class="input" id="password_baru_confirmation" name="password_baru_confirmation" type="password" minlength="8" maxlength="100" autocomplete="new-password" required></div>
            <div class="modal-foot" style="justify-content:flex-start"><button type="submit" class="btn btn-primary"><svg class="ic"><use href="#i-save"/></svg> Simpan Kata Sandi</button></div>
        </form>
    </section>

</div>
@endsection
