
@extends('layouts.internal')

@section('title', 'Profil Petugas — LahanKritisTrack')

@section('content')
<div id="petugas-profile">

    {{-- Judul --}}
    <div class="profile-heading">
        <p class="eyebrow">PENGATURAN AKUN</p>
        <h2>Profil Petugas</h2>
        <p class="subtitle">Kelola informasi pribadi dan keamanan akun Anda.</p>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="notice notice-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice notice-error">
            <strong>Periksa kembali data Anda.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan profil --}}
    <section class="profile-card">
        <div class="profile-summary">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->nama ?? 'P', 0, 1)) }}
            </div>

            <div class="profile-identity">
                <h3>{{ $user->nama }}</h3>
                <p class="username">{{ '@' . $user->username }}</p>
                <span class="role-label">Petugas Lapangan</span>
            </div>

            <div class="account-status">
                <p>Status akun</p>
                <span class="status-label">
                    <span class="status-dot"></span>
                    Aktif
                </span>
            </div>
        </div>
    </section>

    <form action="{{ route('petugas.profil.update') }}"
          method="POST"
          class="profile-form">

        @csrf
        @method('PUT')

        {{-- Informasi akun --}}
        <section class="profile-card">
            <div class="section-heading">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <h3>Informasi Akun</h3>
                    <p>Perbarui nama dan username akun Anda.</p>
                </div>
            </div>

            <div class="section-body">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama"
                           value="{{ old('nama', $user->nama) }}"
                           maxlength="255" autocomplete="name" required
                           placeholder="Masukkan nama lengkap">
                    @error('nama')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="{{ old('username', $user->username) }}"
                           maxlength="255" autocomplete="username" required
                           placeholder="Masukkan username">
                    @error('username')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                    <p class="field-help">
                        ⓘ &nbsp;Username digunakan untuk masuk ke sistem.
                    </p>
                </div>
            </div>
        </section>

        {{-- Keamanan akun --}}
        <section class="profile-card">
            <div class="section-heading">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="11" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        <path d="M12 14v3"/>
                    </svg>
                </div>
                <div>
                    <h3>Keamanan Akun</h3>
                    <p>Ganti kata sandi jika diperlukan.</p>
                </div>
            </div>

            <div class="section-body">
                <div class="password-note">
                    <strong>ⓘ &nbsp;Catatan:</strong>
                    Jika tidak ingin mengganti kata sandi, biarkan semua
                    kolom kata sandi kosong.
                </div>

                <div class="form-group">
                    <label for="password_lama">Kata Sandi Saat Ini</label>
                    <input type="password" id="password_lama"
                           name="password_lama"
                           autocomplete="current-password"
                           placeholder="Masukkan kata sandi saat ini">
                    @error('password_lama')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_baru">Kata Sandi Baru</label>
                    <input type="password" id="password_baru"
                           name="password_baru" minlength="8"
                           autocomplete="new-password"
                           placeholder="Minimal 8 karakter">
                    @error('password_baru')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_baru_confirmation">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input type="password"
                           id="password_baru_confirmation"
                           name="password_baru_confirmation" minlength="8"
                           autocomplete="new-password"
                           placeholder="Ulangi kata sandi baru">
                </div>
            </div>
        </section>

        {{-- Tombol simpan --}}
        <div class="profile-footer">
            <p>ⓘ &nbsp;Pastikan informasi yang dimasukkan sudah benar.</p>

            <button type="submit" class="save-button">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m5 12 4 4L19 6"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>
@endsection

<style>
    /* Tema warna khusus Profil Petugas */
    #petugas-profile {
        width: 100%;
        max-width: 850px;
        margin: 0 auto;
        color: #193c30;
        font-size: 13px;
        line-height: 1.5;
    }

    #petugas-profile * {
        box-sizing: border-box;
    }

    #petugas-profile .profile-heading {
        margin-bottom: 16px;
    }

    #petugas-profile .eyebrow {
        margin: 0 0 4px;
        color: #176b46;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    #petugas-profile .profile-heading h2 {
        margin: 0;
        color: #173d30;
        font-size: 22px;
        font-weight: 750;
        line-height: 1.35;
    }

    #petugas-profile .subtitle {
        margin: 5px 0 0;
        color: #526f61;
        font-size: 12px;
    }

    #petugas-profile .profile-card {
        overflow: hidden;
        margin-bottom: 14px;
        border: 1px solid #d5e7dc;
        border-radius: 10px;
        background: #f8fcf9;
        box-shadow: 0 2px 6px rgb(28 76 49 / 5%);
    }

    #petugas-profile .profile-summary {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 17px 20px;
        background: linear-gradient(110deg, #f8fcf9, #eef8f1);
    }

    #petugas-profile .profile-avatar {
        display: flex;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #39815d;
        color: #fff;
        font-size: 20px;
        font-weight: 600;
    }

    #petugas-profile .profile-identity {
        min-width: 0;
        flex: 1;
    }

    #petugas-profile .profile-identity h3 {
        margin: 0;
        color: #193c30;
        font-size: 15px;
        font-weight: 700;
    }

    #petugas-profile .username {
        margin: 3px 0 7px;
        color: #526f61;
        font-size: 12px;
    }

    #petugas-profile .role-label {
        display: inline-block;
        padding: 4px 9px;
        border: 1px solid #bfe5cd;
        border-radius: 20px;
        background: #e5f6eb;
        color: #176b46;
        font-size: 10px;
        font-weight: 600;
    }

    #petugas-profile .account-status {
        min-width: 105px;
        padding-left: 18px;
        border-left: 1px solid #d7e7dc;
    }

    #petugas-profile .account-status > p {
        margin: 0 0 7px;
        color: #526f61;
        font-size: 11px;
    }

    #petugas-profile .status-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border: 1px solid #c5e9d1;
        border-radius: 20px;
        background: #e8f8ee;
        color: #176b46;
        font-size: 11px;
        font-weight: 600;
    }

    #petugas-profile .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #20965a;
    }

    #petugas-profile .section-heading {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 13px 18px;
        border-bottom: 1px solid #e1ece4;
        background: #f1f8f3;
    }

    #petugas-profile .section-icon {
        display: flex;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #dcefe3;
        color: #176b46;
    }

    #petugas-profile .section-icon svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.7;
    }

    #petugas-profile .section-heading h3 {
        margin: 0;
        color: #193c30;
        font-size: 13px;
        font-weight: 700;
    }

    #petugas-profile .section-heading p {
        margin: 3px 0 0;
        color: #587365;
        font-size: 11px;
    }

    #petugas-profile .section-body {
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding: 16px 18px;
        background: #f8fcf9;
    }

    #petugas-profile .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #294b3b;
        font-size: 12px;
        font-weight: 650;
    }

    #petugas-profile .form-group input {
        display: block;
        width: 100%;
        min-width: 0;
        height: 36px;
        margin: 0;
        padding: 8px 11px;
        border: 1px solid #cbded1;
        border-radius: 6px;
        outline: none;
        background: #fff;
        color: #203d30;
        font-family: inherit;
        font-size: 12px;
        line-height: normal;
        box-shadow: none;
        transition: border-color .15s, box-shadow .15s;
    }

    #petugas-profile .form-group input:focus {
        border-color: #39815d;
        box-shadow: 0 0 0 2px rgb(57 129 93 / 13%);
    }

    #petugas-profile .form-group input::placeholder {
        color: #84978b;
        font-size: 11px;
    }

    #petugas-profile .field-help {
        margin: 5px 0 0;
        color: #526f61;
        font-size: 10px;
    }

    #petugas-profile .field-error {
        margin: 5px 0 0;
        color: #b42332;
        font-size: 11px;
    }

    #petugas-profile .password-note {
        padding: 9px 11px;
        border: 1px solid #f1dda0;
        border-radius: 6px;
        background: #fff8e5;
        color: #745512;
        font-size: 11px;
        line-height: 1.6;
    }

    #petugas-profile .profile-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border: 1px solid #d5e7dc;
        border-radius: 9px;
        background: #f1f8f3;
    }

    #petugas-profile .profile-footer p {
        margin: 0;
        color: #526f61;
        font-size: 10px;
    }

    #petugas-profile .save-button {
        display: inline-flex;
        min-height: 34px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 14px;
        border: 0;
        border-radius: 6px;
        background: #176b46;
        color: #fff;
        font-family: inherit;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }

    #petugas-profile .save-button:hover {
        background: #105537;
    }

    #petugas-profile .save-button svg {
        width: 14px;
        height: 14px;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 2;
    }

    #petugas-profile .notice {
        margin-bottom: 12px;
        padding: 10px 12px;
        border-radius: 7px;
        font-size: 12px;
    }

    #petugas-profile .notice-success {
        border: 1px solid #b9e5c9;
        background: #eaf8ef;
        color: #176b46;
    }

    #petugas-profile .notice-error {
        border: 1px solid #efc5c5;
        background: #fff1f1;
        color: #a52828;
    }

    #petugas-profile .notice-error ul {
        margin: 5px 0 0;
        padding-left: 18px;
        list-style: disc;
    }

    #petugas-profile .notice-error li {
        margin-top: 3px;
    }

    @media (max-width: 600px) {
        #petugas-profile .profile-summary {
            flex-wrap: wrap;
            padding: 14px;
        }

        #petugas-profile .account-status {
            width: 100%;
            margin-top: 3px;
            padding: 10px 0 0;
            border-top: 1px solid #d7e7dc;
            border-left: 0;
        }

        #petugas-profile .section-heading {
            padding: 12px 14px;
        }

        #petugas-profile .section-body {
            padding: 14px;
        }

        #petugas-profile .profile-footer {
            align-items: stretch;
            flex-direction: column;
        }

        #petugas-profile .save-button {
            width: 100%;
        }
    }
</style>
