
<div class="card">
    <div class="card-head">
        <h2>Riwayat Monitoring Lapangan</h2>

        @if(in_array($role, ['admin', 'petugas']))
        <button type="button" class="btn btn-primary" data-open="modal-monitoring" data-title="Catat Laporan Monitoring Lapangan" data-tone="green" data-action="{{ route('monitoring.store') }}" data-method="POST">
            <svg class="ic"><use href="#i-plus"/></svg> Catat Monitoring
        </button>
        @endif
    </div>

    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Dokumentasi</th>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Progres</th>
                    <th>Kondisi Lahan</th>
                    <th>Catatan</th>
                    <th>Dicatat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($monitoring as $m)
                <tr>
                    <td>
                        @if($m['foto'])
                            <a class="thumb" href="{{ $m['foto'] }}" target="_blank" rel="noopener">
                                <img src="{{ $m['foto'] }}" alt="Dokumentasi" loading="lazy">
                            </a>
                        @else
                            <div class="thumb" title="Tanpa foto"></div>
                        @endif
                    </td>

                    <td class="name" style="max-width:280px">
                        {{ $m['kegiatan'] }}
                        <small class="muted" style="display:block;font-weight:500">
                            {{ $m['lokasi'] }}
                        </small>
                    </td>

                    <td class="date">{{ $m['tanggal'] }}</td>

                    <td>
                        <div class="prog-cell">
                            <div class="progress">
                                <i style="width:{{ $m['progres'] }}%"></i>
                            </div>
                            <span>{{ $m['progres'] }}%</span>
                        </div>
                    </td>

                    <td>
                        <span class="badge b-{{ $m['kondisi_class'] }}">
                            {{ $m['kondisi'] }}
                        </span>
                    </td>

                    <td class="muted" style="max-width:290px;font-size:12px">
                        {{ $m['catatan'] }}
                    </td>

                    <td class="muted" style="font-size:12px">
                        {{ $m['petugas'] }}
                    </td>

                    <td>
                        <div class="acts">
                            @if($role === 'admin')
                                <button type="button"
                                    class="act act-ubah"
                                    data-open="modal-monitoring"
                                    data-title="Ubah Laporan Monitoring"
                                    data-tone="amber"
                                    data-fill="{{ $m['fill'] }}"
                                    data-action="{{ $m['url'] }}"
                                    data-method="PUT">
                                    <svg class="ic"><use href="#i-pen"/></svg> Ubah
                                </button>

                                <button type="button"
                                    class="act act-hapus"
                                    data-open="modal-hapus"
                                    data-name="Data monitoring {{ $m['kegiatan'] }} ({{ $m['tanggal'] }})"
                                    data-action="{{ $m['url_destroy'] }}"
                                    data-method="DELETE">
                                    <svg class="ic"><use href="#i-trash"/></svg> Hapus
                                </button>
                            @else
                                <button type="button"
                                    class="act act-upd"
                                    data-open="modal-status-progres"
                                    data-fill="{{ $m['fill_progres'] }}">
                                    <svg class="ic"><use href="#i-refresh"/></svg> Update Status
                                </button>

                                @if($m['can_edit'])
                                    <button type="button"
                                        class="act act-ubah"
                                        data-open="modal-monitoring"
                                        data-title="Ubah Laporan Monitoring"
                                        data-tone="amber"
                                        data-fill="{{ $m['fill'] }}"
                                        data-action="{{ $m['url'] }}"
                                        data-method="PUT">
                                        <svg class="ic"><use href="#i-pen"/></svg> Ubah
                                    </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach

                @if($monitoring->isEmpty())
                    <tr>
                        <td colspan="8" class="empty">
                            Belum ada catatan monitoring.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
