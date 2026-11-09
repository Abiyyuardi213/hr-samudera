@extends('layouts.app')
@section('judul', 'Detail Kantor')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb') @include('kantor.breadcrumb') @endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">Informasi Kantor</h2></div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Kode kantor</dt><dd class="col-sm-9">{{ $kantor->kode_kantor }}</dd>
            <dt class="col-sm-3">Nama kantor</dt><dd class="col-sm-9">{{ $kantor->nama_kantor }}</dd>
            <dt class="col-sm-3">Kota</dt><dd class="col-sm-9">{{ $kantor->kota->nama }} — {{ $kantor->kota->nama_provinsi }}</dd>
            <dt class="col-sm-3">Alamat lengkap</dt><dd class="col-sm-9" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $kantor->alamat_lengkap }}</dd>
            <dt class="col-sm-3">Nomor telepon</dt><dd class="col-sm-9">{{ $kantor->nomor_telepon }}</dd>
            <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $kantor->email }}</dd>
        </dl>
    </div>
    <div class="card-footer aksi"><a href="{{ route('kantor.ubah', $kantor) }}" class="btn btn-primary"><i class="fas fa-edit mr-1" aria-hidden="true"></i>Ubah kantor</a><a href="{{ route('kantor.daftar') }}" class="btn btn-default">Kembali</a></div>
</div>
@endsection
