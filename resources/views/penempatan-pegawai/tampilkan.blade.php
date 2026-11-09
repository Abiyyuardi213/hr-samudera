@extends('layouts.app')
@section('judul', 'Detail Riwayat Penempatan')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('penempatan-pegawai.daftar') }}">Penempatan &amp; Riwayat</a></li><li class="breadcrumb-item active">Detail</li>@endsection
@section('isi')
<div class="card card-primary"><div class="card-header"><h2 class="card-title">{{ $penempatan->pegawai->nama_lengkap }} — {{ $penempatan->pegawai->nip }}</h2></div>
    <div class="card-body"><dl class="row">
        <dt class="col-sm-3">Jenis perubahan</dt><dd class="col-sm-9">{{ $jenisPenempatan[$penempatan->jenis] }}</dd>
        <dt class="col-sm-3">Periode berlaku</dt><dd class="col-sm-9">{{ $penempatan->tanggal_mulai->format('d/m/Y') }} s.d. {{ $penempatan->tanggal_selesai?->format('d/m/Y') ?? 'sekarang' }}</dd>
        <dt class="col-sm-3">Nomor SK</dt><dd class="col-sm-9">{{ $penempatan->nomor_sk ?? '-' }}</dd>
        <dt class="col-sm-3">Dicatat oleh</dt><dd class="col-sm-9">{{ $penempatan->nama_pencatat ?? 'Sistem / migrasi data' }}</dd>
        <dt class="col-sm-3">Waktu pencatatan</dt><dd class="col-sm-9">{{ $penempatan->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
        <dt class="col-sm-3">Catatan</dt><dd class="col-sm-9" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $penempatan->keterangan ?? '-' }}</dd>
    </dl>
    <div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Atribut</th><th>Sebelumnya</th><th>Penempatan ini</th></tr></thead><tbody>
        @foreach (['nama_jabatan' => 'Jabatan', 'nama_unit' => 'Unit organisasi', 'nama_divisi' => 'Divisi', 'nama_kantor' => 'Kantor', 'nama_status_pegawai' => 'Status pegawai', 'nama_shift' => 'Shift kerja'] as $atribut => $label)
            <tr><th>{{ $label }}</th><td>{{ $sebelumnya?->{$atribut} ?? '-' }}</td><td>{{ $penempatan->{$atribut} ?? '-' }}</td></tr>
        @endforeach
        <tr><th>Jam kerja</th><td>{{ $sebelumnya ? substr($sebelumnya->jam_masuk, 0, 5).'–'.substr($sebelumnya->jam_pulang, 0, 5) : '-' }}</td><td>{{ substr($penempatan->jam_masuk, 0, 5) }}–{{ substr($penempatan->jam_pulang, 0, 5) }}</td></tr>
        <tr><th>Ketentuan QR</th><td>{{ $sebelumnya ? ($sebelumnya->wajib_qr ? 'Wajib' : 'Tidak wajib') : '-' }}</td><td>{{ $penempatan->wajib_qr ? 'Wajib' : 'Tidak wajib' }}</td></tr>
    </tbody></table></div>
    <p class="text-muted text-sm mb-0">Nama unit dan rincian shift pada tabel adalah data saat penempatan dicatat.</p>
    </div>
    <div class="card-footer aksi"><a href="{{ route('penempatan-pegawai.daftar', ['pegawai_id' => $penempatan->pegawai_id]) }}" class="btn btn-default">Semua riwayat pegawai</a><a href="{{ route('penempatan-pegawai.daftar', ['pegawai_id' => $penempatan->pegawai_id, 'tambah' => 1]) }}" class="btn btn-primary">Catat penempatan baru</a></div>
</div>
@endsection
