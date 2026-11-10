{{-- Satu baris per KEGIATAN: menampilkan monitoring terbaru. Riwayat lengkap ada di halaman Detail. --}}
<div class="card">
    <div class="card-head">
        <h2>Monitoring Lapangan per Kegiatan</h2>

        @if($role === 'petugas')
        <a href="{{ route('monitoring.create') }}" class="btn btn-primary"><svg class="ic"><use href="#i-plus"/></svg> Catat Monitoring</a>
        @endif
    </div>

    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Dokumentasi</th>
                    <th>Kegiatan</th>
                    <th>Update Terakhir</th>
                    <th>Progres</th>
                    <th>Kondisi Lahan</th>
                    <th>Catatan Terakhir</th>
                    <th>Dicatat Oleh</th>
                    <th>Riwayat</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($monitoring_ringkas as $m)
                @php
                    // id kegiatan: dari data isian progres, cadangannya dicocokkan lewat nama + petak
                    $fp = $m['fill_progres'] ?? null;
                    $fp = is_array($fp) ? $fp : (json_decode((string) $fp, true) ?: []);
                    $idKegiatan = $fp['id_kegiatan']
                        ?? ($kegiatan->first(fn ($k) => $k['nama'] === $m['kegiatan'] && (string) $k['id_lahan'] === (string) $m['id_lahan'])['id'] ?? null);
                @endphp
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
                            {{ $m['lokasi'] }} · Petak #{{ $m['id_lahan'] }}
                        </small>
                    </td>

                    <td class="date">{{ $m['tanggal'] }}</td>

                    <td>
                        <div class="prog-cell">
                            <div class="progress"><i style="width:{{ $m['progres'] }}%"></i></div>
                            <span>{{ $m['progres'] }}%</span>
                        </div>
                    </td>

                    <td><span class="badge b-{{ $m['kondisi_class'] }}">{{ $m['kondisi'] }}</span></td>

                    <td class="muted" style="max-width:260px;font-size:12px;white-space:pre-line">{{ $m['catatan'] }}</td>

                    <td class="muted" style="font-size:12px">{{ $m['petugas'] }}</td>

                    <td class="muted" style="font-size:12px;white-space:nowrap">{{ $m['jumlah'] }} catatan</td>

                    <td>
                        <div class="acts">
                            @if($idKegiatan)
                            <a href="{{ route('monitoring.riwayat', $idKegiatan) }}" class="act act-detail">
                                <svg class="ic"><use href="#i-eye"/></svg> Detail
                            </a>
                            @endif

                            @if($role === 'admin')
                                {{-- Ubah / Hapus bekerja pada catatan monitoring TERBARU; catatan lama ada di Detail --}}
                                <a href="{{ $m['url'] }}/ubah" class="act act-ubah">
                                    <svg class="ic"><use href="#i-pen"/></svg> Ubah
                                </a>

                                {{-- Hapus tetap popup --}}
                                <button type="button" class="act act-hapus"
                                    data-open="modal-hapus"
                                    data-name="Catatan monitoring terbaru {{ $m['kegiatan'] }} ({{ $m['tanggal'] }})"
                                    data-action="{{ $m['url_destroy'] }}" data-method="DELETE">
                                    <svg class="ic"><use href="#i-trash"/></svg> Hapus
                                </button>
                            @else
                                <a href="{{ route('monitoring.create', array_filter(['kegiatan' => $idKegiatan])) }}" class="act act-upd">
                                    <svg class="ic"><use href="#i-refresh"/></svg> Update Status
                                </a>

                                @if($m['can_edit'])
                                    <a href="{{ $m['url'] }}/ubah" class="act act-ubah">
                                        <svg class="ic"><use href="#i-pen"/></svg> Ubah
                                    </a>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach

                @if($monitoring_ringkas->isEmpty())
                    <tr>
                        <td colspan="9" class="empty">
                            Belum ada catatan monitoring.
                            @if($role === 'petugas') Klik “Catat Monitoring” untuk mencatat progres kegiatan Anda. @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>