@extends('layouts.app')
@section('judul', 'Lembur Pegawai')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Catat dan setujui durasi lembur di luar jam kerja terjadwal. Waktu menggunakan WIB.')
@section('isi')
<div class="alert alert-info">Durasi di sini adalah durasi pengajuan yang disetujui, belum merupakan perhitungan upah atau verifikasi realisasi lembur.</div>
<div class="card card-outline card-primary"><div class="card-header"><h2 class="card-title">Daftar Lembur</h2><div class="card-tools"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalLembur">Tambah lembur</button></div></div><div class="card-body">
<form method="GET" class="row align-items-end mb-3"><div class="col-md-3 form-group"><label for="tanggal">Tanggal jadwal</label><input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $tanggal }}" required></div><div class="col-md-4 form-group">@include('layouts.komponen.pilihan-pegawai', ['identitas' => 'filterPegawai'])</div><div class="col-md-3 form-group"><label for="filterStatus">Status</label><select name="status" id="filterStatus" class="form-control"><option value="">Semua</option>@foreach(['menunggu','disetujui','ditolak','dibatalkan'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div><div class="col-md-2 form-group"><button class="btn btn-primary">Filter</button></div></form>
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Pegawai</th><th>Waktu / durasi</th><th>Alasan</th><th>Status / keputusan</th><th>Aksi</th></tr></thead><tbody>
@forelse($daftarLembur as $baris)<tr><td>{{ $baris->jadwalKerja->pegawai->nama_lengkap }}<small class="d-block">{{ $baris->jadwalKerja->pegawai->nip }}</small><small>Pencatat: {{ $baris->nama_pencatat }}</small></td><td>{{ $baris->mulai->format('d/m/Y H:i') }}<br>{{ $baris->selesai->format('d/m/Y H:i') }}<strong class="d-block">{{ intdiv($baris->durasi, 60) }} jam {{ $baris->durasi % 60 }} menit</strong></td><td style="white-space: pre-wrap; overflow-wrap: anywhere">{{ $baris->alasan }}</td><td><span class="badge {{ $baris->status === 'disetujui' ? 'badge-success' : ($baris->status === 'menunggu' ? 'badge-warning' : 'badge-secondary') }}">{{ ucfirst($baris->status) }}</span>@if($baris->nama_pemutus)<small class="d-block">{{ $baris->nama_pemutus }} · {{ $baris->diputus_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</small>{{ $baris->catatan_keputusan }}@endif</td><td>@include('layouts.komponen.tombol-keputusan', ['pengajuan' => $baris, 'tujuan' => route('lembur.keputusan', $baris), 'namaPegawai' => $baris->jadwalKerja->pegawai->nama_lengkap])</td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-4">Belum ada pengajuan lembur pada tanggal ini.</td></tr>@endforelse
</tbody></table></div></div>@include('layouts.komponen.paginasi', ['halaman' => $daftarLembur])</div>
@include('lembur.formulir')
@include('layouts.komponen.keputusan-pengajuan')
@include('layouts.komponen.pilihan-cari')
@endsection
@push('skrip')<script>@if($errors->hasAny(['jadwal_kerja_id', 'mulai', 'selesai', 'alasan'])) $('#modalLembur').modal('show'); @endif</script>@endpush
