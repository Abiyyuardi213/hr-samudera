@extends('layouts.app')
@section('judul', 'Ubah Role')
@section('breadcrumb')
    @include('role.breadcrumb')
@endsection
@section('keterangan', 'Perbarui informasi dan status role.')
@section('isi')
    <div class="row"><div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h2 class="card-title">Formulir Role</h2></div>
            <form method="POST" action="{{ route('role.perbarui', $role) }}">
                @method('PUT')
                @include('role.formulir')
            </form>
        </div>
    </div></div>
@endsection

