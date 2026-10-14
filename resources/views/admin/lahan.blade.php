@extends('layouts.internal')
@section('title', 'Data Lahan Kritis — LahanKritisTrack')

@section('content')
<div class="card">
    <div class="card-head">
        <h2>Data Lahan Kritis per Kecamatan</h2>
        <button type="button" class="btn btn-primary" data-open="modal-import"><svg class="ic"><use href="#i-upload"/></svg> Impor GeoJSON</button>
    </div>
    <div class="table-wrap">
        <table class="tbl">
            <thead><tr><th>Kecamatan</th><th>BPDAS</th><th>Tingkat Kekritisan</th><th>Luas</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($kecamatan as $k)
                <tr>
                    <td class="name">{{ $k['nama'] }}</td>
                    <td class="muted">{{ $k['bpdas'] }}</td>
                    <td><span class="badge b-{{ $k['kritis_class'] }}">{{ $k['kritis'] }}</span></td>
                    <td class="num">{{ $k['luas_fmt'] }} Ha</td>
                    <td><div class="acts">
                        <button type="button" class="act act-detail" data-open="modal-detail" data-detail="{{ $k['detail'] }}"><svg class="ic"><use href="#i-eye"/></svg> Detail</button>
                        <button type="button" class="act act-ubah" data-toast="Ubah data lahan {{ $k['nama'] }} — akan terhubung ke backend"><svg class="ic"><use href="#i-pen"/></svg> Ubah</button>
                    </div></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
@include('partials.modals.detail-lahan')
@include('partials.modals.import')
@endsection

@push('scripts')
<script>window.APP = @json($app);</script>
@endpush
