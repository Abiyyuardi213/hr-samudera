@extends('layouts.app')
@section('judul', 'Tambah Pengguna')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb') @include('pengguna.breadcrumb') @endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">Form Tambah Pengguna</h2></div>
    <form method="POST" action="{{ route('pengguna.simpan') }}" enctype="multipart/form-data">@include('pengguna.formulir')</form>
</div>
@endsection
