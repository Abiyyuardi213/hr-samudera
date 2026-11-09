@extends('layouts.app')
@section('judul', 'Manajemen Pegawai')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Kelola identitas, penempatan, dan informasi kerja pegawai.')
@section('breadcrumb')<li class="breadcrumb-item active">Pegawai</li>@endsection
@section('isi')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header"><h2 class="card-title">Daftar Pegawai</h2><div class="card-tools"><a href="{{ route('pegawai.tambah') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah pegawai</a></div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('pegawai.daftar') }}" class="mb-3"><div class="input-group"><input type="search" name="cari" class="form-control" placeholder="Cari nama, NIP, jabatan, atau kantor" aria-label="Cari pegawai" maxlength="100" value="{{ $pencarian }}"><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div></div></form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover mb-0">
            <thead><tr><th>No</th><th>Nama / NIP</th><th>Jabatan / Divisi</th><th>Kantor</th><th>Status Pegawai</th><th>Shift</th><th>Sisa Cuti</th><th>Aksi</th></tr></thead>
            <tbody>@forelse ($daftarPegawai as $barisPegawai)
                <tr><td>{{ $daftarPegawai->firstItem() + $loop->index }}</td><td>{{ $barisPegawai->nama_lengkap }}<small class="d-block text-muted">{{ $barisPegawai->nip }}</small></td><td>{{ $barisPegawai->jabatan->nama }}<small class="d-block text-muted">{{ $barisPegawai->divisi?->nama ?? 'Tanpa divisi' }}</small></td><td>{{ $barisPegawai->kantor->nama_kantor }}</td><td>{{ $barisPegawai->statusPegawai->nama }}</td><td>{{ $barisPegawai->shiftPegawai->nama_shift }}</td><td>{{ $barisPegawai->sisa_cuti }} hari</td><td><div class="aksi">
                    <a href="{{ route('penempatan-pegawai.daftar', ['pegawai_id' => $barisPegawai->id]) }}" class="btn btn-outline-info btn-sm" aria-label="Riwayat {{ $barisPegawai->nama_lengkap }}"><i class="fas fa-history" aria-hidden="true"></i></a>
                    <a href="{{ route('pegawai.tampilkan', $barisPegawai) }}" class="btn btn-info btn-sm" aria-label="Detail {{ $barisPegawai->nama_lengkap }}"><i class="fas fa-eye" aria-hidden="true"></i></a>
                    <a href="{{ route('pegawai.ubah', $barisPegawai) }}" class="btn btn-warning btn-sm" aria-label="Ubah {{ $barisPegawai->nama_lengkap }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusPegawai" data-tujuan="{{ route('pegawai.hapus', $barisPegawai) }}" data-nama="{{ $barisPegawai->nama_lengkap }}" aria-label="Hapus {{ $barisPegawai->nama_lengkap }}"><i class="fas fa-trash" aria-hidden="true"></i></button>
                </div></td></tr>
            @empty<tr><td colspan="8" class="text-center text-muted py-5">Belum ada pegawai yang sesuai.</td></tr>@endforelse</tbody>
        </table></div>
    </div>
    @include('layouts.komponen.paginasi', ['halaman' => $daftarPegawai, 'satuan' => 'pegawai', 'labelNavigasi' => 'Navigasi halaman pegawai'])
</div>
@include('pegawai.formulir')
<div class="modal fade" id="modalHapusPegawai" tabindex="-1" role="dialog" aria-labelledby="judulHapusPegawai" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header bg-danger"><h2 class="modal-title h5" id="judulHapusPegawai">Hapus Pegawai</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" id="formulirHapusPegawai">@csrf @method('DELETE')<div class="modal-body"><p>Hapus pegawai <strong id="namaPegawaiHapus"></strong>?</p><p class="text-muted mb-0">Data yang dihapus tidak dapat dikembalikan.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, hapus</button></div></form>
</div></div></div>
@endsection
@push('skrip')
<script>
    $('#modalHapusPegawai').on('show.bs.modal', function (kejadian) {
        var tombol = $(kejadian.relatedTarget);
        $('#formulirHapusPegawai').attr('action', tombol.attr('data-tujuan'));
        $('#namaPegawaiHapus').text(tombol.attr('data-nama'));
    });
    @if ($pegawai || request()->boolean('tambah') || $errors->any())
        $('#modalFormulirPegawai').modal('show');
    @endif
</script>
@endpush

