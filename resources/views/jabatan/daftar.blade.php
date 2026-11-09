@extends('layouts.app')
@section('judul', 'Jabatan')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Kelola jabatan, unit organisasi, dan hubungan pelaporan.')
@section('breadcrumb')<li class="breadcrumb-item">Master Office</li><li class="breadcrumb-item active">Jabatan</li>@endsection
@section('isi')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h2 class="card-title">Daftar Jabatan</h2>
        <div class="card-tools"><a href="{{ route('jabatan.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah jabatan</a></div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('jabatan.daftar') }}" class="mb-3">
            <div class="input-group"><input type="search" name="cari" class="form-control" placeholder="Cari jabatan, unit organisasi, atau atasan" aria-label="Cari jabatan" value="{{ $pencarian }}" maxlength="100"><div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div></div>
        </form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover">
            <thead><tr><th>No</th><th>Nama Jabatan</th><th>Unit Organisasi</th><th>Atasan Jabatan</th><th>Bawahan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($daftarJabatan as $barisJabatan)
                    <tr>
                        <td>{{ $daftarJabatan->firstItem() + $loop->index }}</td><td>{{ $barisJabatan->nama }}</td><td>{{ $barisJabatan->unitOrganisasi->nama }}</td>
                        <td>@if ($barisJabatan->atasan)<a href="{{ route('jabatan.tampilkan', $barisJabatan->atasan) }}">{{ $barisJabatan->atasan->nama }}</a>@else<span class="text-muted">Tanpa atasan</span>@endif</td>
                        <td>{{ $barisJabatan->bawahan_count }}</td><td><span class="badge {{ $barisJabatan->status ? 'badge-success' : 'badge-danger' }}">{{ $barisJabatan->status ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td><div class="aksi">
                            <a href="{{ route('jabatan.tampilkan', $barisJabatan) }}" class="btn btn-info btn-sm" aria-label="Detail {{ $barisJabatan->nama }}"><i class="fas fa-eye" aria-hidden="true"></i></a>
                            <a href="{{ route('jabatan.ubah', $barisJabatan) }}" class="btn btn-warning btn-sm" aria-label="Ubah {{ $barisJabatan->nama }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusJabatan" data-tujuan="{{ route('jabatan.hapus', $barisJabatan) }}" data-nama="{{ $barisJabatan->nama }}" aria-label="Hapus {{ $barisJabatan->nama }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">Belum ada jabatan yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
        <span class="text-muted text-sm">{{ $daftarJabatan->total() }} jabatan &middot; Halaman {{ $daftarJabatan->currentPage() }} dari {{ $daftarJabatan->lastPage() }}</span>
        <nav aria-label="Navigasi halaman jabatan"><ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $daftarJabatan->onFirstPage() ? 'disabled' : '' }}">@if ($daftarJabatan->previousPageUrl())<a class="page-link" href="{{ $daftarJabatan->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif</li>
            <li class="page-item {{ $daftarJabatan->hasMorePages() ? '' : 'disabled' }}">@if ($daftarJabatan->nextPageUrl())<a class="page-link" href="{{ $daftarJabatan->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif</li>
        </ul></nav>
    </div>
</div>
@include('jabatan.formulir')
<div class="modal fade" id="modalHapusJabatan" tabindex="-1" role="dialog" aria-labelledby="judulHapusJabatan" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header bg-danger"><h2 class="modal-title h5" id="judulHapusJabatan">Hapus Jabatan</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" id="formulirHapusJabatan">@csrf @method('DELETE')
        <div class="modal-body"><p>Hapus jabatan <strong id="namaJabatanHapus"></strong>?</p><p class="text-muted mb-0">Jabatan yang masih memiliki bawahan tidak dapat dihapus.</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, hapus</button></div>
    </form>
</div></div></div>
@endsection
@push('skrip')
<script>
    $('#modalHapusJabatan').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapusJabatan').attr('action', tombol.attr('data-tujuan'));
        $('#namaJabatanHapus').text(tombol.attr('data-nama'));
    });
    @if ($jabatan || request()->boolean('tambah') || $errors->hasAny(['nama', 'unit_organisasi_id', 'atasan_id', 'deskripsi', 'status']))
        $('#modalFormulirJabatan').modal('show');
    @endif
</script>
@endpush


