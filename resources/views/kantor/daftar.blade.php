@extends('layouts.app')
@section('judul', 'Daftar Kantor')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')
    @include('kantor.breadcrumb')
@endsection
@section('keterangan', 'Kelola kantor dan informasi kontak perusahaan.')
@section('isi')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h2 class="card-title">Daftar Kantor</h2>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahKantor"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah kantor</button>
            </div>
        </div>
        <div class="card-body">
            <form class="mb-3" method="GET" action="{{ route('kantor.daftar') }}">
                <div class="input-group"><input type="search" name="cari" class="form-control" placeholder="Cari nama, kode kantor, atau kota" aria-label="Cari kantor" value="{{ $pencarian }}" maxlength="100"><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div></div>
            </form>
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead><tr><th>No</th><th>Kode Kantor</th><th>Nama Kantor</th><th>Kota</th><th>Telepon</th><th>Email</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($daftarKantor as $kantor)
                        <tr>
                            <td>{{ $daftarKantor->firstItem() + $loop->index }}</td>
                            <td>{{ $kantor->kode_kantor }}</td><td>{{ $kantor->nama_kantor }}</td>
                            <td>{{ $kantor->kota->nama }}<small class="d-block text-muted">{{ $kantor->kota->nama_provinsi }}</small></td>
                            <td>{{ $kantor->nomor_telepon }}</td><td>{{ $kantor->email }}</td>
                            <td>
                                <div class="aksi">
                                    <a href="{{ route('kantor.tampilkan', $kantor) }}" class="btn btn-info btn-sm" aria-label="Detail kantor {{ $kantor->nama_kantor }}" title="Detail"><i class="fas fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('kantor.ubah', $kantor) }}" class="btn btn-warning btn-sm" aria-label="Ubah kantor {{ $kantor->nama_kantor }}" title="Ubah"><i class="fas fa-edit" aria-hidden="true"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#konfirmasiHapus" data-tujuan="{{ route('kantor.hapus', $kantor) }}" data-nama="{{ $kantor->nama_kantor }}" aria-label="Hapus kantor {{ $kantor->nama_kantor }}" title="Hapus"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-building fa-2x d-block mb-3" aria-hidden="true"></i>Belum ada kantor.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap">
            <span class="text-muted text-sm">{{ $daftarKantor->total() }} kantor &middot; Halaman {{ $daftarKantor->currentPage() }} dari {{ $daftarKantor->lastPage() }}</span>
            <nav aria-label="Navigasi halaman kantor" class="mt-2 mt-sm-0">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $daftarKantor->onFirstPage() ? 'disabled' : '' }}">
                        @if ($daftarKantor->previousPageUrl())<a class="page-link" href="{{ $daftarKantor->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif
                    </li>
                    <li class="page-item {{ $daftarKantor->hasMorePages() ? '' : 'disabled' }}">
                        @if ($daftarKantor->nextPageUrl())<a class="page-link" href="{{ $daftarKantor->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="modal fade" id="konfirmasiHapus" tabindex="-1" role="dialog" aria-labelledby="judulKonfirmasiHapus" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h2 class="modal-title h5" id="judulKonfirmasiHapus">Hapus Kantor</h2>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="POST" id="formulirHapus">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Hapus kantor <strong id="namaRoleHapus"></strong>?</p>
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
    @include('kantor.modal-tambah')
@endsection
@push('skrip')
<script>
    $('#konfirmasiHapus').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapus').attr('action', tombol.attr('data-tujuan'));
        $('#namaRoleHapus').text(tombol.attr('data-nama'));
    });
    @if (request()->boolean('tambah') || $errors->hasAny(['nama_kantor', 'kota_id', 'alamat_lengkap', 'nomor_telepon', 'email']))
        $('#modalTambahKantor').modal('show');
    @endif
</script>
@endpush

