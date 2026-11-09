@extends('layouts.app')
@section('judul', 'Manajemen Shift')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Kelola jam kerja dan ketentuan QR untuk setiap shift.')
@section('breadcrumb')<li class="breadcrumb-item">Master Office</li><li class="breadcrumb-item active">Manajemen Shift</li>@endsection
@section('isi')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header"><h2 class="card-title">Daftar Shift</h2><div class="card-tools"><a href="{{ route('shift-pegawai.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah shift</a></div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('shift-pegawai.daftar') }}" class="mb-3"><div class="input-group"><input type="search" name="cari" class="form-control" placeholder="Cari nama shift" aria-label="Cari shift" value="{{ $pencarian }}" maxlength="100"><div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div></div></form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover mb-0">
            <thead><tr><th>No</th><th>Nama Shift</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Durasi</th><th>Wajib QR</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($daftarShift as $barisShift)
                <tr><td>{{ $daftarShift->firstItem() + $loop->index }}</td><td>{{ $barisShift->nama_shift }}</td><td>{{ substr($barisShift->jam_masuk, 0, 5) }}</td><td>{{ substr($barisShift->jam_pulang, 0, 5) }} @if (strcmp($barisShift->jam_pulang, $barisShift->jam_masuk) < 0)<small class="d-block text-muted">Hari berikutnya</small>@endif</td><td>{{ $barisShift->durasiTerbaca() }}</td><td><span class="badge {{ $barisShift->wajib_qr ? 'badge-primary' : 'badge-secondary' }}">{{ $barisShift->wajib_qr ? 'Wajib' : 'Tidak wajib' }}</span></td><td><div class="aksi">
                    <a href="{{ route('shift-pegawai.ubah', $barisShift) }}" class="btn btn-warning btn-sm" aria-label="Ubah {{ $barisShift->nama_shift }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusShift" data-tujuan="{{ route('shift-pegawai.hapus', $barisShift) }}" data-nama="{{ $barisShift->nama_shift }}" aria-label="Hapus {{ $barisShift->nama_shift }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                </div></td></tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada shift yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap"><span class="text-muted text-sm">{{ $daftarShift->total() }} shift &middot; Halaman {{ $daftarShift->currentPage() }} dari {{ $daftarShift->lastPage() }}</span>
        <nav aria-label="Navigasi halaman shift"><ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $daftarShift->onFirstPage() ? 'disabled' : '' }}">@if ($daftarShift->previousPageUrl())<a class="page-link" href="{{ $daftarShift->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif</li>
            <li class="page-item {{ $daftarShift->hasMorePages() ? '' : 'disabled' }}">@if ($daftarShift->nextPageUrl())<a class="page-link" href="{{ $daftarShift->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif</li>
        </ul></nav>
    </div>
</div>
@include('shift-pegawai.formulir')
<div class="modal fade" id="modalHapusShift" tabindex="-1" role="dialog" aria-labelledby="judulHapusShift" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header bg-danger"><h2 class="modal-title h5" id="judulHapusShift">Hapus Shift</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" id="formulirHapusShift">@csrf @method('DELETE')<div class="modal-body"><p>Hapus shift <strong id="namaShiftHapus"></strong>?</p><p class="text-muted mb-0">Data yang dihapus tidak dapat dikembalikan.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, hapus</button></div></form>
</div></div></div>
@endsection
@push('skrip')
<script>
    $('#modalHapusShift').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapusShift').attr('action', tombol.attr('data-tujuan'));
        $('#namaShiftHapus').text(tombol.attr('data-nama'));
    });
    @if ($shift || request()->boolean('tambah') || $errors->hasAny(['nama_shift', 'jam_masuk', 'jam_pulang', 'wajib_qr']))
        $('#modalFormulirShift').modal('show');
    @endif
</script>
@endpush

