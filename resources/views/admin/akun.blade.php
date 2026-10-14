@extends('layouts.internal')
@section('title', 'Manajemen Akun — LahanKritisTrack')

@section('content')
<div class="card">
    <div class="card-head">
        <h2>Daftar Akun Pengguna System</h2>
        <button type="button" class="btn btn-primary" data-open="modal-akun" data-title="Tambah Akun Pengguna System" data-tone="green"><svg class="ic"><use href="#i-plus"/></svg> Tambah Akun</button>
    </div>
    <div class="table-wrap">
        <table class="tbl">
            <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td class="name">{{ $u['nama'] }}</td>
                    <td class="mono muted">{{ $u['username'] }}</td>
                    <td><span class="badge b-{{ $u['role_class'] }}">{{ $u['role'] }}</span></td>
                    <td class="date">{{ $u['dibuat'] }}</td>
                    <td><div class="acts">
                        <button type="button" class="act act-ubah" data-open="modal-akun" data-title="Ubah Akun Pengguna" data-tone="amber" data-fill="{{ $u['fill'] }}"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                        <button type="button" class="act act-hapus" data-open="modal-hapus" data-name="Akun {{ $u['nama'] }}"><svg class="ic"><use href="#i-trash"/></svg> Hapus</button>
                    </div></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
@include('partials.modals.akun')
@include('partials.modals.hapus')
@endsection
