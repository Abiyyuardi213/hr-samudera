@extends('layouts.app')
@section('judul', 'Detail Unit Organisasi')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')<li class="breadcrumb-item">Data Master</li><li class="breadcrumb-item"><a href="{{ route('unit-organisasi.daftar') }}">Unit Organisasi</a></li><li class="breadcrumb-item active">Detail</li>@endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">{{ $unit->nama }}</h2></div>
    <div class="card-body"><dl class="row mb-0">
        <dt class="col-sm-3">Nama unit</dt><dd class="col-sm-9">{{ $unit->nama }}</dd>
        <dt class="col-sm-3">Jenis unit</dt><dd class="col-sm-9">{{ $jenisUnit[$unit->jenis_unit] }}</dd>
        <dt class="col-sm-3">Induk unit</dt><dd class="col-sm-9">@if ($unit->induk)<a href="{{ route('unit-organisasi.tampilkan', $unit->induk) }}">{{ $unit->induk->nama }}</a>@else Tingkat teratas @endif</dd>
        <dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $unit->deskripsi ?? '-' }}</dd>
        <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge {{ $unit->status ? 'badge-success' : 'badge-danger' }}">{{ $unit->status ? 'Aktif' : 'Nonaktif' }}</span></dd>
    </dl></div>
    <div class="card-footer aksi"><a href="{{ route('unit-organisasi.ubah', $unit) }}" class="btn btn-primary">Ubah unit</a><a href="{{ route('unit-organisasi.daftar') }}" class="btn btn-default">Kembali</a></div>
</div>
<div class="card card-outline card-primary">
    <div class="card-header"><h2 class="card-title">Unit Anak Langsung ({{ $daftarAnak->total() }})</h2></div>
    <div class="card-body table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Nama Unit</th><th>Jenis</th><th>Status</th></tr></thead><tbody>
    @forelse ($daftarAnak as $anak)<tr><td><a href="{{ route('unit-organisasi.tampilkan', $anak) }}">{{ $anak->nama }}</a></td><td>{{ $jenisUnit[$anak->jenis_unit] }}</td><td>{{ $anak->status ? 'Aktif' : 'Nonaktif' }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">Belum ada unit anak.</td></tr>@endforelse
    </tbody></table></div>
    <div class="card-footer d-flex justify-content-between"><span>Halaman {{ $daftarAnak->currentPage() }} dari {{ $daftarAnak->lastPage() }}</span><div>@if ($daftarAnak->previousPageUrl())<a href="{{ $daftarAnak->previousPageUrl() }}" class="btn btn-default btn-sm">Sebelumnya</a>@endif @if ($daftarAnak->nextPageUrl())<a href="{{ $daftarAnak->nextPageUrl() }}" class="btn btn-default btn-sm">Berikutnya</a>@endif</div></div>
</div>
@endsection
