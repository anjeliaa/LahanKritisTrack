<div class="card" data-table-filter="tbl-kegiatan">
    <div class="card-head">
        <h2>Kegiatan Rehabilitasi Lahan</h2>
        <button type="button" class="btn btn-primary" data-open="modal-kegiatan" data-title="Tambah Kegiatan Rehabilitasi Baru" data-tone="green" data-action="{{ route('kegiatan.store') }}" data-method="POST"><svg class="ic"><use href="#i-plus"/></svg> Tambah Kegiatan</button>
    </div>
    <div class="toolbar">
        <div class="search"><svg class="ic"><use href="#i-search"/></svg><input class="input tf-q" type="search" placeholder="Cari kegiatan..."></div>
        <select class="select tool-select tf-kec"><option value="">Semua Kecamatan</option>
            @foreach($kecamatan as $c)
                    <option value="{{ $c['nama'] }}">{{ $c['nama'] }}</option>
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
            <thead><tr><th>Kegiatan</th><th>Kecamatan</th><th>Penanggung Jawab</th><th>Mulai</th><th>Selesai</th><th>Progres</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($kegiatan as $k)
                <tr data-q="{{ $k['q'] }}" data-kec="{{ $k['kecamatan'] }}" data-status="{{ $k['status'] }}">
                    <td class="name" style="max-width:340px">{{ $k['nama'] }}@unless($k['published'])<small class="muted" style="display:block;font-weight:500">Belum dipublikasikan</small>@endunless</td>
                    <td>{{ $k['kecamatan'] }}<small class="muted" style="display:block">Petak #{{ $k['id_lahan'] }}</small></td>
                    <td class="muted">{{ $k['pj'] }}</td>
                    <td class="date">{{ $k['mulai'] }}</td>
                    <td class="date-end">{{ $k['selesai'] }}</td>
                    <td><div class="prog-cell"><div class="progress"><i style="width:{{ $k['progres'] }}%"></i></div><span>{{ $k['progres'] }}%</span></div></td>
                    <td><span class="badge b-{{ $k['status_class'] }}">{{ $k['status'] }}</span></td>
                    <td>
                        <div class="acts">
                            @if($role === 'admin')
                            <button type="button" class="act act-ubah" data-open="modal-kegiatan" data-title="Ubah Kegiatan Rehabilitasi" data-tone="amber" data-fill="{{ $k['fill'] }}" data-action="{{ $k['url'] }}" data-method="PUT"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                            <button type="button" class="act act-hapus" data-open="modal-hapus" data-name="{{ $k['nama'] }}" data-action="{{ $k['url_destroy'] }}" data-method="DELETE"><svg class="ic"><use href="#i-trash"/></svg> Hapus</button>
                            @else
                            <button type="button" class="act act-upd" data-open="modal-status-kegiatan" data-fill="{{ $k['fill_status'] }}"><svg class="ic"><use href="#i-refresh"/></svg> Update Status</button>
                            <button type="button" class="act act-ubah" data-open="modal-kegiatan" data-title="Ubah Kegiatan Rehabilitasi" data-tone="amber" data-fill="{{ $k['fill'] }}" data-action="{{ $k['url'] }}" data-method="PUT"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($kegiatan->isEmpty())
                <tr><td colspan="8" class="empty">Belum ada kegiatan rehabilitasi. Klik “Tambah Kegiatan” untuk memulai.</td></tr>
                @endif
                <tr class="empty-row" style="display:none"><td colspan="8" class="empty">Tidak ada kegiatan yang cocok.</td></tr>
            </tbody>
        </table>
    </div>
</div>
