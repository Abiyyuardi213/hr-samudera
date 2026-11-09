@extends('layouts.app')
@section('judul', 'Ubah Pengguna')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb') @include('pengguna.breadcrumb') @endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">Form Ubah Pengguna</h2></div>
    <form method="POST" action="{{ route('pengguna.perbarui', $pengguna) }}" enctype="multipart/form-data">@method('PUT') @include('pengguna.formulir')</form>
</div>
@endsection
