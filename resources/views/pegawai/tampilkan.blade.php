@extends('layouts.app')
@section('judul', 'Detail Pegawai')
@section('kelas-judul', 'font-weight-bold')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('pegawai.daftar') }}">Pegawai</a></li><li class="breadcrumb-item active">Detail</li>@endsection
@section('isi')
<div class="card card-primary"><div class="card-header"><h2 class="card-title">{{ $pegawai->nama_lengkap }}</h2></div>
<div class="card-body"><dl class="row mb-0">
    @foreach (['Nama lengkap' => $pegawai->nama_lengkap, 'NIP' => $pegawai->nip, 'Jabatan' => $pegawai->jabatan->nama, 'Divisi' => $pegawai->divisi?->nama ?? 'Tanpa divisi', 'Kantor' => $pegawai->kantor->nama_kantor, 'Status pegawai' => $pegawai->statusPegawai->nama, 'NIK' => $pegawai->nik, 'Tempat, tanggal lahir' => $pegawai->tempat_lahir.', '.$pegawai->tanggal_lahir->format('d/m/Y'), 'Jenis kelamin' => $pegawai->jenis_kelamin === 'laki_laki' ? 'Laki-laki' : 'Perempuan', 'Alamat domisili' => $pegawai->alamat_domisili, 'Tanggal masuk' => $pegawai->tanggal_masuk->format('d/m/Y'), 'Shift kerja' => $pegawai->shiftPegawai->nama_shift.' ('.substr($pegawai->shiftPegawai->jam_masuk, 0, 5).'–'.substr($pegawai->shiftPegawai->jam_pulang, 0, 5).')', 'Sisa cuti' => $pegawai->sisa_cuti.' hari', 'Nomor telepon' => $pegawai->nomor_telepon, 'Email' => $pegawai->email] as $label => $nilai)
        <dt class="col-sm-4 mb-2">{{ $label }}</dt><dd class="col-sm-8" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $nilai }}</dd>
    @endforeach
</dl></div><div class="card-footer aksi"><a href="{{ route('penempatan-pegawai.daftar', ['pegawai_id' => $pegawai->id]) }}" class="btn btn-info">Riwayat penempatan</a><a href="{{ route('pegawai.ubah', $pegawai) }}" class="btn btn-primary">Ubah pegawai</a><a href="{{ route('pegawai.daftar') }}" class="btn btn-default">Kembali</a></div></div>
@endsection

