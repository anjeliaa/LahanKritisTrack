<div class="card" data-table-filter="tbl-kegiatan">
    <div class="card-head">
        <h2>Kegiatan Rehabilitasi Lahan</h2>
        <a href="{{ route('kegiatan.create') }}" class="btn btn-primary"><svg class="ic"><use href="#i-plus"/></svg> Tambah Kegiatan</a>
    </div>
    <div class="toolbar">
        <div class="search"><svg class="ic"><use href="#i-search"/></svg><input class="input tf-q" type="search" placeholder="Cari kegiatan..."></div>
        <select class="select tool-select tf-kec"><option value="">Semua Kecamatan</option>
            @foreach($kecamatan as $c)
                    <option value="{{ $c['nama'] }}">{{ $c['nama'] }}@unless($c['ada_data']) (belum ada data)@endunless</option>
                    @endforeach
        </select>
        <select class="select tool-select tf-status"><option value="">Semua Status</option>
            @foreach($status_opts as $s)
            <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="table-wrap">
        <table class="tbl" id="tbl-kegiatan">
            <thead><tr><th>Kegiatan</th><th>Kecamatan</th><th>Penanggung Jawab</th>@if($role === 'admin')<th>Petugas</th>@endif<th>Mulai</th><th>Target Selesai</th><th>Progres</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($kegiatan as $k)
                <tr data-q="{{ $k['q'] }}" data-kec="{{ $k['kecamatan'] }}" data-status="{{ $k['status'] }}">
                    <td class="name" style="max-width:340px">{{ $k['nama'] }}@unless($k['published'])<small class="muted" style="display:block;font-weight:500">Belum dipublikasikan</small>@endunless</td>
                    <td>{{ $k['kecamatan'] }}<small class="muted" style="display:block">Petak #{{ $k['id_lahan'] }}</small></td>
                    <td class="muted">{{ $k['pj'] }}</td>
                    @if($role === 'admin')<td class="muted">{{ $k['pengelola'] ?? 'Belum ditugaskan' }}</td>@endif
                    <td class="date">{{ $k['mulai'] }}</td>
                    <td class="date-end">{{ $k['selesai'] }}</td>
                    <td><div class="prog-cell"><div class="progress"><i style="width:{{ $k['progres'] }}%"></i></div><span>{{ $k['progres'] }}%</span></div></td>
                    <td><span class="badge b-{{ $k['status_class'] }}">{{ $k['status'] }}</span>@if($k['selesai_aktual'])<small class="muted" style="display:block;font-weight:500;margin-top:3px">pada {{ $k['selesai_aktual'] }}</small>@endif</td>
                    <td>
                        <div class="acts">
                            @if($role === 'admin')
                            <a href="{{ route('kegiatan.edit', $k['id']) }}" class="act act-ubah"><svg class="ic"><use href="#i-pen"/></svg> Ubah</a>
                            <button type="button" class="act act-hapus" data-open="modal-hapus" data-name="{{ $k['nama'] }}" data-action="{{ $k['url_destroy'] }}" data-method="DELETE"><svg class="ic"><use href="#i-trash"/></svg> Hapus</button>
                            @else
                            <a href="{{ route('kegiatan.status.edit', $k['id']) }}" class="act act-upd"><svg class="ic"><use href="#i-refresh"/></svg> Update Status</a>
                            <a href="{{ route('kegiatan.edit', $k['id']) }}" class="act act-ubah"><svg class="ic"><use href="#i-pen"/></svg> Ubah</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($kegiatan->isEmpty())
                <tr><td colspan="{{ $role === 'admin' ? 9 : 8 }}" class="empty">@if($role === 'admin')Belum ada kegiatan rehabilitasi. Klik “Tambah Kegiatan” untuk memulai.@else Belum ada kegiatan yang menjadi tanggung jawab Anda. Anda dapat menambah kegiatan baru atau meminta Admin menugaskan kegiatan kepada Anda.@endif</td></tr>
                @endif
                <tr class="empty-row" style="display:none"><td colspan="{{ $role === 'admin' ? 9 : 8 }}" class="empty">Tidak ada kegiatan yang cocok.</td></tr>
            </tbody>
        </table>
    </div>
</div>