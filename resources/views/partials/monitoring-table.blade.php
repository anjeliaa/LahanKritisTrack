<div class="card">
    <div class="card-head">
        <h2>Riwayat Monitoring Lapangan</h2>
        @if($role === 'petugas')
        <button type="button" class="btn btn-primary" data-open="modal-monitoring" data-title="Catat Laporan Monitoring Lapangan" data-tone="green"><svg class="ic"><use href="#i-plus"/></svg> Catat Monitoring</button>
        @endif
    </div>
    <div class="table-wrap">
        <table class="tbl">
            <thead><tr><th>Dokumentasi</th><th>Kegiatan</th><th>Tanggal</th><th>Progres</th><th>Kondisi Lahan</th><th>Catatan</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($monitoring as $m)
                <tr>
                    <td><div class="thumb" style="--c:{{ $m['warna'] }}"></div></td>
                    <td class="name" style="max-width:280px">{{ $m['kegiatan'] }}</td>
                    <td class="date">{{ $m['tanggal'] }}</td>
                    <td><div class="prog-cell"><div class="progress"><i style="width:{{ $m['progres'] }}%"></i></div><span>{{ $m['progres'] }}%</span></div></td>
                    <td><span class="badge b-membaik">{{ $m['kondisi'] }}</span></td>
                    <td class="muted" style="max-width:290px;font-size:12px">{{ $m['catatan'] }}</td>
                    <td>
                        <div class="acts">
                            @if($role === 'admin')
                            <button type="button" class="act act-ubah" data-open="modal-monitoring" data-title="Ubah Laporan Monitoring" data-tone="amber" data-fill="{{ $m['fill'] }}"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                            <button type="button" class="act act-hapus" data-open="modal-hapus" data-name="Data monitoring {{ $m['kegiatan'] }}"><svg class="ic"><use href="#i-trash"/></svg> Hapus</button>
                            @else
                            <button type="button" class="act act-upd" data-open="modal-status-progres" data-fill="{{ $m['fill'] }}"><svg class="ic"><use href="#i-refresh"/></svg> Update Status</button>
                            <button type="button" class="act act-ubah" data-open="modal-monitoring" data-title="Ubah Laporan Monitoring" data-tone="amber" data-fill="{{ $m['fill'] }}"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
