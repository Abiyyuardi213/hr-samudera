<div class="aksi">
@foreach(($pengajuan->status === 'menunggu' ? ['disetujui' => 'Setujui', 'ditolak' => 'Tolak', 'dibatalkan' => 'Batalkan'] : ($pengajuan->status === 'disetujui' ? ['dibatalkan' => 'Batalkan'] : [])) as $keputusan => $teks)
    <button type="button" class="btn btn-sm {{ $keputusan === 'disetujui' ? 'btn-success' : 'btn-outline-danger' }}" data-toggle="modal" data-target="#modalKeputusan" data-tujuan="{{ $tujuan }}" data-keputusan="{{ $keputusan }}" data-ringkasan="{{ $teks }} pengajuan {{ $namaPegawai }}?">{{ $teks }}</button>
@endforeach
</div>
@include('layouts.komponen.riwayat-keputusan')
