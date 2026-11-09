@extends('layouts.app')
@section('judul', 'Daftar Role')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')
    @include('role.breadcrumb')
@endsection
@section('keterangan', 'Kelola role yang digunakan dalam sistem HR perusahaan.')
@section('isi')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h2 class="card-title">Daftar Role</h2>
            <div class="card-tools">
                <a href="{{ route('role.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah role</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead><tr><th>No</th><th>Nama</th><th>Deskripsi</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($daftarRole as $role)
                        <tr>
                            <td>{{ $daftarRole->firstItem() + $loop->index }}</td>
                            <td>{{ $role->nama }}</td>
                            <td class="deskripsi-role">{{ $role->deskripsi ?? '-' }}</td>
                            <td><span class="badge {{ $role->status ? 'badge-success' : 'badge-danger' }} px-2 py-1">{{ $role->status ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="aksi">
                                    <a href="{{ route('role.tampilkan', $role) }}" class="btn btn-info btn-sm" aria-label="Detail role {{ $role->nama }}" title="Detail"><i class="fas fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('role.ubah', $role) }}" class="btn btn-warning btn-sm" aria-label="Ubah role {{ $role->nama }}" title="Ubah"><i class="fas fa-edit" aria-hidden="true"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#konfirmasiHapus" data-tujuan="{{ route('role.hapus', $role) }}" data-nama="{{ $role->nama }}" aria-label="Hapus role {{ $role->nama }}" title="Hapus"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-user-shield fa-2x d-block mb-3" aria-hidden="true"></i>Belum ada role.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap">
            <span class="text-muted text-sm">{{ $daftarRole->total() }} role &middot; Halaman {{ $daftarRole->currentPage() }} dari {{ $daftarRole->lastPage() }}</span>
            <nav aria-label="Navigasi halaman role" class="mt-2 mt-sm-0">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $daftarRole->onFirstPage() ? 'disabled' : '' }}">
                        @if ($daftarRole->previousPageUrl())<a class="page-link" href="{{ $daftarRole->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif
                    </li>
                    <li class="page-item {{ $daftarRole->hasMorePages() ? '' : 'disabled' }}">
                        @if ($daftarRole->nextPageUrl())<a class="page-link" href="{{ $daftarRole->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="modal fade" id="konfirmasiHapus" tabindex="-1" role="dialog" aria-labelledby="judulKonfirmasiHapus" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h2 class="modal-title h5" id="judulKonfirmasiHapus">Hapus Role</h2>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="POST" id="formulirHapus">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Hapus role <strong id="namaRoleHapus"></strong>?</p>
                        <p class="text-muted mb-0">Data yang dihapus tidak dapat dikembalikan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1" aria-hidden="true"></i>Ya, hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('skrip')
<script>
    $('#konfirmasiHapus').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapus').attr('action', tombol.attr('data-tujuan'));
        $('#namaRoleHapus').text(tombol.attr('data-nama'));
    });
</script>
@endpush
