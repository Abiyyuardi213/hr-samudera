@extends('layouts.app')
@section('judul', 'Daftar Pengguna')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')
    @include('pengguna.breadcrumb')
@endsection
@section('keterangan', 'Kelola akun, role, dan status pengguna sistem HR.')
@section('isi')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h2 class="card-title">Daftar Pengguna</h2>
            <div class="card-tools">
                <a href="{{ route('pengguna.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah pengguna</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead><tr><th>No</th><th>Foto</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($daftarPengguna as $pengguna)
                        <tr>
                            <td>{{ $daftarPengguna->firstItem() + $loop->index }}</td>
                            <td>@if ($pengguna->foto_profil)<img src="{{ asset('storage/'.$pengguna->foto_profil) }}" alt="Foto {{ $pengguna->name }}" class="img-circle" width="40" height="40" style="object-fit: cover;">@else<i class="fas fa-user-circle fa-2x text-muted" aria-hidden="true"></i>@endif</td><td>{{ $pengguna->name }}</td>
                            <td>{{ $pengguna->email }}</td><td>{{ $pengguna->role?->nama ?? '-' }}</td>
                            <td><span class="badge {{ $pengguna->status ? 'badge-success' : 'badge-danger' }} px-2 py-1">{{ $pengguna->status ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="aksi">
                                    <a href="{{ route('pengguna.tampilkan', $pengguna) }}" class="btn btn-info btn-sm" aria-label="Detail pengguna {{ $pengguna->name }}" title="Detail"><i class="fas fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('pengguna.ubah', $pengguna) }}" class="btn btn-warning btn-sm" aria-label="Ubah pengguna {{ $pengguna->name }}" title="Ubah"><i class="fas fa-edit" aria-hidden="true"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#konfirmasiHapus" data-tujuan="{{ route('pengguna.hapus', $pengguna) }}" data-nama="{{ $pengguna->name }}" aria-label="Hapus pengguna {{ $pengguna->name }}" title="Hapus"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-user-shield fa-2x d-block mb-3" aria-hidden="true"></i>Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap">
            <span class="text-muted text-sm">{{ $daftarPengguna->total() }} pengguna &middot; Halaman {{ $daftarPengguna->currentPage() }} dari {{ $daftarPengguna->lastPage() }}</span>
            <nav aria-label="Navigasi halaman pengguna" class="mt-2 mt-sm-0">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $daftarPengguna->onFirstPage() ? 'disabled' : '' }}">
                        @if ($daftarPengguna->previousPageUrl())<a class="page-link" href="{{ $daftarPengguna->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif
                    </li>
                    <li class="page-item {{ $daftarPengguna->hasMorePages() ? '' : 'disabled' }}">
                        @if ($daftarPengguna->nextPageUrl())<a class="page-link" href="{{ $daftarPengguna->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="modal fade" id="konfirmasiHapus" tabindex="-1" role="dialog" aria-labelledby="judulKonfirmasiHapus" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h2 class="modal-title h5" id="judulKonfirmasiHapus">Hapus Pengguna</h2>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="POST" id="formulirHapus">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Hapus pengguna <strong id="namaRoleHapus"></strong>?</p>
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

