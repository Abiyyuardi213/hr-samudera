@extends('layouts.app')
@section('judul', 'Tambah Role')
@section('breadcrumb')
    @include('role.breadcrumb')
@endsection
@section('keterangan', 'Tambahkan role baru untuk kebutuhan perusahaan.')
@section('isi')
    <div class="row"><div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h2 class="card-title">Formulir Role</h2></div>
            <form method="POST" action="{{ route('role.simpan') }}">
                @include('role.formulir')
            </form>
        </div>
    </div></div>
@endsection

