@extends('layouts.app')
@section('judul', 'Status Pegawai')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Kelola jenis status kepegawaian perusahaan.')
@section('breadcrumb')<li class="breadcrumb-item">Master Office</li><li class="breadcrumb-item active">Status Pegawai</li>@endsection
@section('isi')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header"><h2 class="card-title">Daftar Status Pegawai</h2><div class="card-tools"><a href="{{ route('status-pegawai.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah status</a></div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('status-pegawai.daftar') }}" class="mb-3"><div class="input-group"><input type="search" name="cari" class="form-control" placeholder="Cari nama atau kode status" aria-label="Cari status pegawai" value="{{ $pencarian }}" maxlength="100"><div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div></div></form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover mb-0">
            <thead><tr><th>No</th><th>Kode</th><th>Nama Status</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($daftarStatus as $barisStatus)
                <tr><td>{{ $daftarStatus->firstItem() + $loop->index }}</td><td>{{ $barisStatus->kode }}</td><td>{{ $barisStatus->nama }}</td><td style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $barisStatus->deskripsi ?? '-' }}</td><td><div class="aksi">
                    <a href="{{ route('status-pegawai.ubah', $barisStatus) }}" class="btn btn-warning btn-sm" aria-label="Ubah {{ $barisStatus->nama }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusStatus" data-tujuan="{{ route('status-pegawai.hapus', $barisStatus) }}" data-nama="{{ $barisStatus->nama }}" aria-label="Hapus {{ $barisStatus->nama }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                </div></td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada status pegawai yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap"><span class="text-muted text-sm">{{ $daftarStatus->total() }} status &middot; Halaman {{ $daftarStatus->currentPage() }} dari {{ $daftarStatus->lastPage() }}</span>
        <nav aria-label="Navigasi halaman status pegawai"><ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $daftarStatus->onFirstPage() ? 'disabled' : '' }}">@if ($daftarStatus->previousPageUrl())<a class="page-link" href="{{ $daftarStatus->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif</li>
            <li class="page-item {{ $daftarStatus->hasMorePages() ? '' : 'disabled' }}">@if ($daftarStatus->nextPageUrl())<a class="page-link" href="{{ $daftarStatus->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif</li>
        </ul></nav>
    </div>
</div>
@include('status-pegawai.formulir')
<div class="modal fade" id="modalHapusStatus" tabindex="-1" role="dialog" aria-labelledby="judulHapusStatus" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header bg-danger"><h2 class="modal-title h5" id="judulHapusStatus">Hapus Status Pegawai</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" id="formulirHapusStatus">@csrf @method('DELETE')<div class="modal-body"><p>Hapus status <strong id="namaStatusHapus"></strong>?</p><p class="text-muted mb-0">Data yang dihapus tidak dapat dikembalikan.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, hapus</button></div></form>
</div></div></div>
@endsection
@push('skrip')
<script>
    $('#modalHapusStatus').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapusStatus').attr('action', tombol.attr('data-tujuan'));
        $('#namaStatusHapus').text(tombol.attr('data-nama'));
    });
    @if ($statusPegawai || request()->boolean('tambah') || $errors->hasAny(['nama', 'deskripsi']))
        $('#modalFormulirStatus').modal('show');
    @endif
</script>
@endpush
