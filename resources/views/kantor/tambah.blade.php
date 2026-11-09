@extends('layouts.app')
@section('judul', 'Tambah Kantor')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb') @include('kantor.breadcrumb') @endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">Form Tambah Kantor</h2></div>
    <form method="POST" action="{{ route('kantor.simpan') }}">@include('kantor.formulir')</form>
</div>
@endsection
