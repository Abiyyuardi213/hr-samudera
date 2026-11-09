@extends('layouts.app')
@section('judul', 'Detail Jabatan')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')<li class="breadcrumb-item">Master Office</li><li class="breadcrumb-item"><a href="{{ route('jabatan.daftar') }}">Jabatan</a></li><li class="breadcrumb-item active">Detail</li>@endsection
@section('isi')
<div class="card card-primary">
    <div class="card-header"><h2 class="card-title">{{ $jabatan->nama }}</h2></div>
    <div class="card-body"><dl class="row mb-0">
        <dt class="col-sm-3">Nama jabatan</dt><dd class="col-sm-9">{{ $jabatan->nama }}</dd>
        <dt class="col-sm-3">Unit organisasi</dt><dd class="col-sm-9">{{ $jabatan->unitOrganisasi->nama }}</dd>
        <dt class="col-sm-3">Atasan jabatan</dt><dd class="col-sm-9">@if ($jabatan->atasan)<a href="{{ route('jabatan.tampilkan', $jabatan->atasan) }}">{{ $jabatan->atasan->nama }}</a>@else Tanpa atasan @endif</dd>
        <dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $jabatan->deskripsi ?? '-' }}</dd>
        <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge {{ $jabatan->status ? 'badge-success' : 'badge-danger' }}">{{ $jabatan->status ? 'Aktif' : 'Nonaktif' }}</span></dd>
    </dl></div>
    <div class="card-footer aksi"><a href="{{ route('jabatan.ubah', $jabatan) }}" class="btn btn-primary">Ubah jabatan</a><a href="{{ route('jabatan.daftar') }}" class="btn btn-default">Kembali</a></div>
</div>
<div class="card card-outline card-primary">
    <div class="card-header"><h2 class="card-title">Jabatan Bawahan Langsung ({{ $daftarBawahan->total() }})</h2></div>
    <div class="card-body table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Nama Jabatan</th><th>Status</th></tr></thead><tbody>
    @forelse ($daftarBawahan as $bawahan)<tr><td><a href="{{ route('jabatan.tampilkan', $bawahan) }}">{{ $bawahan->nama }}</a></td><td>{{ $bawahan->status ? 'Aktif' : 'Nonaktif' }}</td></tr>@empty<tr><td colspan="2" class="text-center text-muted">Belum ada jabatan bawahan.</td></tr>@endforelse
    </tbody></table></div>
    <div class="card-footer d-flex justify-content-between"><span>Halaman {{ $daftarBawahan->currentPage() }} dari {{ $daftarBawahan->lastPage() }}</span><div>@if ($daftarBawahan->previousPageUrl())<a href="{{ $daftarBawahan->previousPageUrl() }}" class="btn btn-default btn-sm">Sebelumnya</a>@endif @if ($daftarBawahan->nextPageUrl())<a href="{{ $daftarBawahan->nextPageUrl() }}" class="btn btn-default btn-sm">Berikutnya</a>@endif</div></div>
</div>
@endsection


