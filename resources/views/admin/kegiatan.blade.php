@extends('layouts.internal')
@section('title', 'Kegiatan Rehabilitasi — LahanKritisTrack')

@section('content')
@include('partials.kegiatan-table')
@endsection

@section('modals')
@include('partials.modals.hapus')
@endsection