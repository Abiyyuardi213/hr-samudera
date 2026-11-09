@extends('layouts.app')
@section('judul', 'Beranda')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')<li class="breadcrumb-item active">Beranda</li>@endsection
@section('isi')
<div class="card card-primary card-outline">
    <div class="card-body">
        <h2 class="h4">Selamat datang, {{ auth()->user()->name }}.</h2>
        <p class="text-muted">Anda masuk sebagai {{ auth()->user()->role?->nama }}.</p>
        @if (auth()->user()->dapatMengelola())
            <a href="{{ route('pengguna.daftar') }}" class="btn btn-primary"><i class="fas fa-users mr-1"></i>Kelola Pengguna</a>
            <a href="{{ route('role.daftar') }}" class="btn btn-default">Kelola Role</a>
        @endif
    </div>
</div>
@endsection
