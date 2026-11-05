@extends('layouts.internal')
@section('title', 'Monitoring Lapangan — LahanKritisTrack')

@section('content')
@include('partials.monitoring-table')
@endsection

@section('modals')
@include('partials.modals.hapus')
@endsection