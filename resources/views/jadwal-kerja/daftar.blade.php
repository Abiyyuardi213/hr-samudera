@extends('layouts.app')
@section('judul', 'Jadwal Kerja Pegawai')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Atur jadwal harian, shift lintas tengah malam, dan hari libur pegawai. Seluruh waktu menggunakan WIB.')
@section('isi')
<div class="card card-outline card-primary">
    <div class="card-header"><h2 class="card-title">Daftar Jadwal</h2><div class="card-tools"><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalJadwal"><i class="fas fa-plus mr-1"></i>Buat jadwal</button></div></div>
    <div class="card-body">
        <form method="GET" class="row align-items-end mb-3"><div class="col-md-3 form-group"><label for="bulan">Bulan</label><input type="month" id="bulan" name="bulan" value="{{ $bulan }}" class="form-control" required></div><div class="col-md-6 form-group">@include('layouts.komponen.pilihan-pegawai', ['identitas' => 'filterPegawai'])</div><div class="col-md-3 form-group"><button class="btn btn-primary">Filter</button> <a class="btn btn-default" href="{{ route('jadwal-kerja.daftar') }}">Reset</a></div></form>
        <div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Tanggal</th><th>Pegawai</th><th>Shift / kantor</th><th>Jam kerja (WIB)</th><th>Keterangan</th><th>Aksi</th></tr></thead><tbody>
        @forelse($daftarJadwal as $baris)
        <tr><td>{{ $baris->tanggal->format('d/m/Y') }}</td><td>{{ $baris->pegawai->nama_lengkap }}<small class="d-block text-muted">{{ $baris->pegawai->nip }}</small></td><td><span class="badge {{ $baris->libur ? 'badge-secondary' : 'badge-info' }}">{{ $baris->libur ? 'Libur' : $baris->nama_shift }}</span><small class="d-block">{{ $baris->nama_kantor }}</small></td><td>{{ $baris->mulai?->format('d/m H:i') ?? '—' }}<br>{{ $baris->selesai?->format('d/m H:i') }}</td><td>{{ $baris->keterangan ?? '—' }}</td><td><div class="aksi"><a href="{{ route('jadwal-kerja.daftar', ['bulan' => $bulan, 'ubah' => $baris->id, 'pegawai_id' => request('pegawai_id')]) }}" class="btn btn-warning btn-sm">Ubah</a><a href="{{ route('absensi.daftar', ['tanggal' => $baris->tanggal->toDateString(), 'pegawai_id' => $baris->pegawai_id]) }}" class="btn btn-info btn-sm">Absensi</a><button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapusJadwal" data-tujuan="{{ route('jadwal-kerja.hapus', $baris) }}" data-ringkasan="{{ $baris->pegawai->nama_lengkap }} · {{ $baris->tanggal->format('d/m/Y') }}">Hapus</button></div></td></tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada jadwal pada periode ini.</td></tr>@endforelse
        </tbody></table></div>
    </div>@include('layouts.komponen.paginasi', ['halaman' => $daftarJadwal])
</div>
@include('jadwal-kerja.formulir')
<div class="modal fade" id="modalHapusJadwal" tabindex="-1" aria-labelledby="judulHapusJadwal" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger"><h2 id="judulHapusJadwal" class="modal-title h5">Hapus Jadwal</h2><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button></div><form method="POST" id="formulirHapusJadwal">@csrf @method('DELETE')<div class="modal-body"><p id="ringkasanHapus"></p><p>Hapus jadwal ini? Jadwal yang sudah memiliki catatan operasional tetap dilindungi.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-danger">Hapus</button></div></form></div></div></div>
@include('layouts.komponen.pilihan-cari')
@endsection
@push('skrip')
<script>
$('#modalHapusJadwal').on('show.bs.modal', function (kejadian) { var tombol = $(kejadian.relatedTarget); $('#formulirHapusJadwal').attr('action', tombol.attr('data-tujuan')); $('#ringkasanHapus').text(tombol.attr('data-ringkasan')); });
@if($jadwal || ($errors->any() && ! $errors->has('jadwal'))) $('#modalJadwal').modal('show'); @endif
</script>
@endpush
