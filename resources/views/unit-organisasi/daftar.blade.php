@extends('layouts.app')
@section('judul', 'Unit Organisasi')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Kelola unit dan hubungan induk–anak dalam struktur organisasi.')
@section('breadcrumb')<li class="breadcrumb-item">Data Master</li><li class="breadcrumb-item active">Unit Organisasi</li>@endsection
@section('isi')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h2 class="card-title">Daftar Unit Organisasi</h2>
        <div class="card-tools"><a href="{{ route('unit-organisasi.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah unit</a></div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('unit-organisasi.daftar') }}" class="mb-3">
            <div class="input-group"><input type="search" name="cari" class="form-control" placeholder="Cari nama unit atau induk unit" aria-label="Cari unit organisasi" value="{{ $pencarian }}" maxlength="100"><div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div></div>
        </form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover">
            <thead><tr><th>No</th><th>Nama Unit</th><th>Jenis</th><th>Induk Unit</th><th>Unit Anak</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($daftarUnit as $barisUnit)
                    <tr>
                        <td>{{ $daftarUnit->firstItem() + $loop->index }}</td><td>{{ $barisUnit->nama }}</td><td>{{ $jenisUnit[$barisUnit->jenis_unit] }}</td>
                        <td>@if ($barisUnit->induk)<a href="{{ route('unit-organisasi.tampilkan', $barisUnit->induk) }}">{{ $barisUnit->induk->nama }}</a>@else<span class="text-muted">Tingkat teratas</span>@endif</td>
                        <td>{{ $barisUnit->anak_count }}</td><td><span class="badge {{ $barisUnit->status ? 'badge-success' : 'badge-danger' }}">{{ $barisUnit->status ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td><div class="aksi">
                            <a href="{{ route('unit-organisasi.tampilkan', $barisUnit) }}" class="btn btn-info btn-sm" aria-label="Detail {{ $barisUnit->nama }}"><i class="fas fa-eye" aria-hidden="true"></i></a>
                            <a href="{{ route('unit-organisasi.ubah', $barisUnit) }}" class="btn btn-warning btn-sm" aria-label="Ubah {{ $barisUnit->nama }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusUnit" data-tujuan="{{ route('unit-organisasi.hapus', $barisUnit) }}" data-nama="{{ $barisUnit->nama }}" aria-label="Hapus {{ $barisUnit->nama }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">Belum ada unit organisasi yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
        <span class="text-muted text-sm">{{ $daftarUnit->total() }} unit &middot; Halaman {{ $daftarUnit->currentPage() }} dari {{ $daftarUnit->lastPage() }}</span>
        <nav aria-label="Navigasi halaman unit"><ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $daftarUnit->onFirstPage() ? 'disabled' : '' }}">@if ($daftarUnit->previousPageUrl())<a class="page-link" href="{{ $daftarUnit->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif</li>
            <li class="page-item {{ $daftarUnit->hasMorePages() ? '' : 'disabled' }}">@if ($daftarUnit->nextPageUrl())<a class="page-link" href="{{ $daftarUnit->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif</li>
        </ul></nav>
    </div>
</div>
@include('unit-organisasi.formulir')
<div class="modal fade" id="modalHapusUnit" tabindex="-1" role="dialog" aria-labelledby="judulHapusUnit" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header bg-danger"><h2 class="modal-title h5" id="judulHapusUnit">Hapus Unit Organisasi</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" id="formulirHapusUnit">@csrf @method('DELETE')
        <div class="modal-body"><p>Hapus unit <strong id="namaUnitHapus"></strong>?</p><p class="text-muted mb-0">Unit yang masih memiliki anak tidak dapat dihapus.</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, hapus</button></div>
    </form>
</div></div></div>
@endsection
@push('skrip')
<script>
    $('#modalHapusUnit').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapusUnit').attr('action', tombol.attr('data-tujuan'));
        $('#namaUnitHapus').text(tombol.attr('data-nama'));
    });
    @if ($unit || request()->boolean('tambah') || $errors->hasAny(['nama', 'jenis_unit', 'induk_id', 'deskripsi', 'status']))
        $('#modalFormulirUnit').modal('show');
    @endif
</script>
@endpush
