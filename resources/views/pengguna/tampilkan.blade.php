@extends('layouts.app')
@section('judul', 'Detail Pengguna')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb') @include('pengguna.breadcrumb') @endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">Informasi Pengguna</h2></div>
    <div class="card-body">
        @if ($pengguna->foto_profil)<img src="{{ asset('storage/'.$pengguna->foto_profil) }}" alt="Foto profil {{ $pengguna->name }}" width="100" height="100" class="img-circle mb-3" style="object-fit: cover;">@endif
        <dl class="row">
            <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $pengguna->name }}</dd>
            <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $pengguna->email }}</dd>
            <dt class="col-sm-3">Role</dt><dd class="col-sm-9">{{ $pengguna->role?->nama ?? '-' }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge {{ $pengguna->status ? 'badge-success' : 'badge-danger' }}">{{ $pengguna->status ? 'Aktif' : 'Nonaktif' }}</span></dd>
        </dl>
    </div>
    <div class="card-footer aksi"><a class="btn btn-primary" href="{{ route('pengguna.ubah', $pengguna) }}">Ubah</a><a class="btn btn-default" href="{{ route('pengguna.daftar') }}">Kembali</a></div>
</div>
@endsection
