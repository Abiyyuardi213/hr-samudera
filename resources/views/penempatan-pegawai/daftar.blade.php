@extends('layouts.app')
@section('judul', 'Penempatan & Riwayat Pegawai')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Catat perubahan penempatan dan telusuri riwayat kerja pegawai.')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('pegawai.daftar') }}">Pegawai</a></li><li class="breadcrumb-item active">Penempatan &amp; Riwayat</li>@endsection
@section('isi')
@if ($pegawaiDipilih)
<div class="card card-outline card-info">
    <div class="card-header"><h2 class="card-title">{{ $pegawaiDipilih->nama_lengkap }} <small class="text-muted">{{ $pegawaiDipilih->nip }}</small></h2><div class="card-tools"><a href="{{ route('pegawai.tampilkan', $pegawaiDipilih) }}" class="btn btn-default btn-sm">Detail pegawai</a></div></div>
    <div class="card-body"><h3 class="h6 font-weight-bold">Penempatan saat ini</h3><div class="row">
        <div class="col-md-3 mb-2"><small class="text-muted d-block">Jabatan / Divisi</small>{{ $pegawaiDipilih->jabatan->nama }}<small class="d-block">{{ $pegawaiDipilih->divisi?->nama ?? 'Tanpa divisi' }}</small></div>
        <div class="col-md-3 mb-2"><small class="text-muted d-block">Kantor</small>{{ $pegawaiDipilih->kantor->nama_kantor }}</div>
        <div class="col-md-3 mb-2"><small class="text-muted d-block">Status pegawai</small>{{ $pegawaiDipilih->statusPegawai->nama }}</div>
        <div class="col-md-3 mb-2"><small class="text-muted d-block">Shift</small>{{ $pegawaiDipilih->shiftPegawai->nama_shift }}</div>
    </div></div>
</div>
@endif
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header"><h2 class="card-title">Riwayat Penempatan</h2><div class="card-tools"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPenempatan" @disabled($daftarPegawai->isEmpty())><i class="fas fa-plus mr-1" aria-hidden="true"></i>Catat penempatan</button></div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('penempatan-pegawai.daftar') }}" class="mb-3"><div class="row">
            <div class="col-md-4 form-group"><label for="filterCari" class="sr-only">Cari riwayat</label><input id="filterCari" type="search" name="cari" class="form-control" placeholder="Nama, NIP, kantor, jabatan, atau SK" value="{{ $pencarian }}" maxlength="100"></div>
            <div class="col-md-4 form-group"><label for="filterPegawai" class="sr-only">Filter pegawai</label><select id="filterPegawai" name="pegawai_id" class="form-control"><option value="">Semua pegawai</option>@foreach ($daftarPegawai as $pilihan)<option value="{{ $pilihan->id }}" @selected($pegawaiDipilih?->id === $pilihan->id)>{{ $pilihan->nip }} — {{ $pilihan->nama_lengkap }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label for="filterJenis" class="sr-only">Jenis perubahan</label><select id="filterJenis" name="jenis" class="form-control"><option value="">Semua jenis</option>@foreach ($jenisPenempatan as $nilai => $label)<option value="{{ $nilai }}" @selected($jenisDipilih === $nilai)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><button type="submit" class="btn btn-primary">Filter</button> <a href="{{ route('penempatan-pegawai.daftar') }}" class="btn btn-default" aria-label="Reset filter">Reset</a></div>
        </div></form>
        <div class="table-responsive"><table class="table table-bordered table-striped table-hover mb-0">
            <thead><tr><th>Pegawai</th><th>Periode</th><th>Jenis / SK</th><th>Penempatan</th><th>Shift / Status</th><th>Aksi</th></tr></thead>
            <tbody>@forelse ($daftarRiwayat as $riwayat)
                <tr><td><a href="{{ route('penempatan-pegawai.daftar', ['pegawai_id' => $riwayat->pegawai_id]) }}">{{ $riwayat->pegawai->nama_lengkap }}</a><small class="d-block text-muted">{{ $riwayat->pegawai->nip }}</small></td>
                <td>{{ $riwayat->tanggal_mulai->format('d/m/Y') }}<small class="d-block">s.d. {{ $riwayat->tanggal_selesai?->format('d/m/Y') ?? 'sekarang' }}</small><span class="badge {{ $riwayat->tanggal_selesai ? 'badge-secondary' : 'badge-success' }}">{{ $riwayat->tanggal_selesai ? 'Selesai' : 'Terakhir' }}</span></td>
                <td>{{ $jenisPenempatan[$riwayat->jenis] }}<small class="d-block text-muted">{{ $riwayat->nomor_sk ?? '-' }}</small></td>
                <td>{{ $riwayat->nama_jabatan }}<small class="d-block">{{ $riwayat->nama_divisi ?? 'Tanpa divisi' }}</small><small class="d-block text-muted">{{ $riwayat->nama_kantor }}</small></td>
                <td>{{ $riwayat->nama_shift }}<small class="d-block">{{ substr($riwayat->jam_masuk, 0, 5) }}–{{ substr($riwayat->jam_pulang, 0, 5) }}</small><small class="d-block text-muted">{{ $riwayat->nama_status_pegawai }}</small></td>
                <td><a href="{{ route('penempatan-pegawai.tampilkan', $riwayat) }}" class="btn btn-info btn-sm">Detail</a></td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-5">Belum ada riwayat yang sesuai. Tambahkan pegawai untuk membuat penempatan awal.</td></tr>@endforelse</tbody>
        </table></div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap"><span class="text-muted text-sm">{{ $daftarRiwayat->total() }} catatan &middot; Halaman {{ $daftarRiwayat->currentPage() }} dari {{ $daftarRiwayat->lastPage() }}</span><nav aria-label="Halaman riwayat"><ul class="pagination pagination-sm mb-0">
        <li class="page-item {{ $daftarRiwayat->onFirstPage() ? 'disabled' : '' }}">@if ($daftarRiwayat->previousPageUrl())<a class="page-link" href="{{ $daftarRiwayat->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif</li>
        <li class="page-item {{ $daftarRiwayat->hasMorePages() ? '' : 'disabled' }}">@if ($daftarRiwayat->nextPageUrl())<a class="page-link" href="{{ $daftarRiwayat->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif</li>
    </ul></nav></div>
</div>
<p class="text-muted text-sm">Riwayat disimpan sebagai catatan berurutan. Gunakan penempatan baru untuk mencatat perubahan berikutnya.</p>
@include('penempatan-pegawai.formulir')
@endsection
@push('skrip')
<script>
    @if (request()->boolean('tambah') || $errors->any())
        $('#modalPenempatan').modal('show');
    @endif
</script>
@endpush
