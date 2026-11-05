@extends('layouts.internal')

@php
    $edit      = $item !== null;
    $judul     = $edit ? 'Ubah Kegiatan Rehabilitasi' : 'Tambah Kegiatan Rehabilitasi';
    $listRoute = $role === 'admin' ? 'admin.kegiatan' : 'petugas.kegiatan';
    $tgl       = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d') : '';

    $petakAll     = collect($petak);
    $lahanPilih   = old('id_lahan', $item->id_lahan ?? request('lahan'));
    $petakPilih   = $petakAll->firstWhere('id', (int) $lahanPilih);
    $kecPilih     = old('kecamatan_id', $petakPilih['kecamatan_id'] ?? (collect($kecamatan_data)->first()['id'] ?? null));

    $statusList   = $edit ? $status_opts : ['Rencana', 'Berjalan'];
    $statusPilih  = old('status', $item->status ?? 'Rencana');
    $publishPilih = (int) (bool) old('is_published', $item->is_published ?? 1);
@endphp

@section('title', $judul . ' — LahanKritisTrack')

@section('page_header')
<section class="page-header">
    <div class="ph-left">
        <div class="ph-icon"><svg class="ic" aria-hidden="true"><use href="#i-sprout"/></svg></div>
        <div>
            <div class="ph-path">Kegiatan Rehabilitasi</div>
            <h1 class="ph-title">{{ $judul }}</h1>
            <p class="ph-desc">{{ $edit ? 'Perbarui data kegiatan, lalu simpan perubahan.' : 'Isi data kegiatan rehabilitasi pada satu petak lahan.' }}</p>
        </div>
    </div>
    <a href="{{ route($listRoute) }}" class="btn btn-outline"><svg class="ic" aria-hidden="true"><use href="#i-arrowleft"/></svg> Kembali ke daftar</a>
</section>
@endsection

@section('content')
<form method="POST" action="{{ $edit ? route('kegiatan.update', $item) : route('kegiatan.store') }}" class="card form-card" id="form-kegiatan">
    @csrf
    @if($edit) @method('PUT') @endif

    @if($errors->any())
    <div class="form-alert">Ada {{ $errors->count() }} isian yang perlu diperbaiki. Periksa kolom yang bertanda merah.</div>
    @endif

    {{-- 1. Informasi --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Informasi Kegiatan</h2>
            <p>Nama, jenis, dan pelaksana kegiatan rehabilitasi.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="nama_kegiatan">Nama Kegiatan</label>
                <input id="nama_kegiatan" class="input @error('nama_kegiatan') err @enderror" name="nama_kegiatan" value="{{ old('nama_kegiatan', $item->nama_kegiatan ?? '') }}" placeholder="mis. Penanaman Vegetasi DAS" maxlength="255" required>
                @error('nama_kegiatan')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="jenis_kegiatan">Jenis Rehabilitasi</label>
                <input id="jenis_kegiatan" class="input @error('jenis_kegiatan') err @enderror" name="jenis_kegiatan" value="{{ old('jenis_kegiatan', $item->jenis_kegiatan ?? '') }}" placeholder="mis. Reboisasi Hutan / Vegetasi" maxlength="255">
                @error('jenis_kegiatan')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="penanggung_jawab">Penanggung Jawab / Pelaksana</label>
                <input id="penanggung_jawab" class="input @error('penanggung_jawab') err @enderror" name="penanggung_jawab" value="{{ old('penanggung_jawab', $item->penanggung_jawab ?? '') }}" placeholder="Kelompok Tani / CV" maxlength="255">
                @error('penanggung_jawab')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 2. Lokasi --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Lokasi Lahan</h2>
            <p>Kegiatan menempel pada satu petak lahan. Nomor petak sama dengan yang tampil di peta.</p>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="kecamatan_id">Kecamatan</label>
                <select id="kecamatan_id" class="select" name="kecamatan_id" data-cascade="id_lahan" required>
                    @foreach($kecamatan_data as $c)
                    <option value="{{ $c['id'] }}" @selected((string) $kecPilih === (string) $c['id'])>{{ $c['nama'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="id_lahan">Petak Lahan</label>
                <select id="id_lahan" class="select @error('id_lahan') err @enderror" name="id_lahan" required>
                    @foreach($petak as $p)
                    <option value="{{ $p['id'] }}" data-kec="{{ $p['kecamatan_id'] }}" @selected((string) $lahanPilih === (string) $p['id'])>{{ $p['label'] }}</option>
                    @endforeach
                </select>
                @error('id_lahan')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 3. Target & jadwal --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Target &amp; Jadwal</h2>
            <p>Tanggal penyelesaian sebenarnya tercatat otomatis saat kegiatan berstatus Selesai.</p>
        </div>
        <div class="form-grid">
            <div class="field span2">
                <label for="target_luas">Target Luas (Ha)</label>
                <input id="target_luas" class="input @error('target_luas') err @enderror" name="target_luas" type="number" step="0.01" min="0" value="{{ old('target_luas', $item->target_luas ?? '') }}" placeholder="mis. 12.5" required>
                @error('target_luas')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="tanggal_mulai">Tanggal Mulai</label>
                <input id="tanggal_mulai" class="input @error('tanggal_mulai') err @enderror" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', $tgl($item->tanggal_mulai ?? null)) }}">
                @error('tanggal_mulai')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="tanggal_selesai">Target Tanggal Selesai</label>
                <input id="tanggal_selesai" class="input @error('tanggal_selesai') err @enderror" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', $tgl($item->tanggal_selesai ?? null)) }}">
                @error('tanggal_selesai')<div class="field-err">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    {{-- 4. Pengelolaan --}}
    <section class="form-sec">
        <div class="form-sec-head">
            <h2>Pengelolaan</h2>
            <p>Status, penanggung jawab lapangan, dan tampilan ke masyarakat.</p>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="status">Status</label>
                <select id="status" class="select @error('status') err @enderror" name="status">
                    @foreach($statusList as $s)
                    <option value="{{ $s }}" @selected($statusPilih === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <div class="hint">
                    @if($edit) Status harus sejalan dengan progres monitoring terbaru. Selesai hanya bisa dipilih bila progres 100%.
                    @else Kegiatan baru belum punya progres, jadi hanya Rencana atau Berjalan. @endif
                </div>
                @error('status')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="is_published">Tampil ke Masyarakat?</label>
                <select id="is_published" class="select" name="is_published">
                    <option value="1" @selected($publishPilih === 1)>Ya, publikasikan</option>
                    <option value="0" @selected($publishPilih === 0)>Tidak</option>
                </select>
            </div>
            @if($role === 'admin')
            <div class="field span2">
                <label for="id_user">Petugas Penanggung Jawab</label>
                <select id="id_user" class="select @error('id_user') err @enderror" name="id_user">
                    <option value="">— Belum ditugaskan —</option>
                    @foreach($petugas_opts as $p)
                    <option value="{{ $p['id'] }}" @selected((string) old('id_user', $item->id_user ?? '') === (string) $p['id'])>{{ $p['nama'] }}</option>
                    @endforeach
                </select>
                <div class="hint">Petugas ini yang berwenang memperbarui status dan mencatat monitoring kegiatan.</div>
                @error('id_user')<div class="field-err">{{ $message }}</div>@enderror
            </div>
            @endif
        </div>
    </section>

    <div class="form-foot">
        <a href="{{ route($listRoute) }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary"><svg class="ic" aria-hidden="true"><use href="#i-save"/></svg> {{ $edit ? 'Simpan Perubahan' : 'Simpan Kegiatan' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    // saring daftar petak sesuai kecamatan terpilih (logika cascade ada di app.js)
    var kec = document.getElementById('kecamatan_id');
    if (kec) kec.dispatchEvent(new Event('change', { bubbles: true }));

    // cegah klik ganda saat menyimpan
    var f = document.getElementById('form-kegiatan');
    f.addEventListener('submit', function () {
        var b = f.querySelector('button[type=submit]');
        if (b) setTimeout(function () { b.disabled = true; }, 0);
    });
})();
</script>
@endpush