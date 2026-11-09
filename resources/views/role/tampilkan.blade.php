@extends('layouts.app')
@section('judul', 'Detail Role')
@section('breadcrumb')
    @include('role.breadcrumb')
@endsection
@section('keterangan', 'Informasi role dalam sistem HR perusahaan.')
@section('isi')
    <div class="row"><div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h2 class="card-title">Informasi Role</h2></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $role->nama }}</dd>
                    <dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9 deskripsi-role">{{ $role->deskripsi ?? '-' }}</dd>
                    <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge {{ $role->status ? 'badge-success' : 'badge-danger' }}">{{ $role->status ? 'Aktif' : 'Nonaktif' }}</span></dd>
                </dl>
            </div>
            <div class="card-footer aksi">
                <a href="{{ route('role.ubah', $role) }}" class="btn btn-primary"><i class="fas fa-pen mr-1" aria-hidden="true"></i>Ubah role</a>
                <a href="{{ route('role.daftar') }}" class="btn btn-default">Kembali</a>
            </div>
        </div>
    </div></div>
@endsection

