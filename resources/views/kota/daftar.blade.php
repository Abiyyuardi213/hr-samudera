@extends('layouts.app')
@section('judul', 'Daftar Kota')
@section('kelas-judul', 'font-weight-bold')
@section('keterangan', 'Data kabupaten dan kota seluruh Indonesia.')
@section('breadcrumb')
    <li class="breadcrumb-item">Data Master</li><li class="breadcrumb-item active">Kota</li>
@endsection
@section('isi')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h2 class="card-title">Daftar Kabupaten/Kota</h2>
            <div class="card-tools"><button type="button" class="btn btn-primary btn-sm" id="tombolSinkron"><i class="fas fa-sync-alt mr-1" aria-hidden="true"></i>Sinkron Kota</button></div>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 text-muted text-sm mb-2 mb-md-0">
                    <strong>{{ $jumlahKota }}</strong> kabupaten/kota tersimpan.
                    <br>Sinkron terakhir: {{ $sinkronTerakhir ? \Illuminate\Support\Carbon::parse($sinkronTerakhir)->format('d/m/Y H:i') : 'Belum pernah' }}
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('kota.daftar') }}">
                        <div class="input-group">
                            <input type="search" name="cari" class="form-control" placeholder="Cari kota, provinsi, atau kode" aria-label="Cari kota, provinsi, atau kode" value="{{ $pencarian }}" maxlength="100">
                            <div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button></div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0">
                    <thead><tr><th>No</th><th>Kode Wilayah</th><th>Kabupaten/Kota</th><th>Provinsi</th></tr></thead>
                    <tbody>
                        @forelse ($daftarKota as $kota)
                            <tr><td>{{ $daftarKota->firstItem() + $loop->index }}</td><td>{{ $kota->kode }}</td><td>{{ $kota->nama }}</td><td>{{ $kota->nama_provinsi }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">{{ $jumlahKota ? 'Tidak ada kota yang sesuai pencarian.' : 'Belum ada data kota. Klik Sinkron Kota untuk memuat data seluruh Indonesia.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap">
            <span class="text-muted text-sm">{{ $daftarKota->total() }} hasil &middot; Halaman {{ $daftarKota->currentPage() }} dari {{ $daftarKota->lastPage() }}</span>
            <nav aria-label="Navigasi halaman kota"><ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $daftarKota->onFirstPage() ? 'disabled' : '' }}">@if ($daftarKota->previousPageUrl())<a class="page-link" href="{{ $daftarKota->previousPageUrl() }}">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif</li>
                <li class="page-item {{ $daftarKota->hasMorePages() ? '' : 'disabled' }}">@if ($daftarKota->nextPageUrl())<a class="page-link" href="{{ $daftarKota->nextPageUrl() }}">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif</li>
            </ul></nav>
        </div>
    </div>
    <p class="text-muted text-sm">Sumber data: <a href="https://www.emsifa.com/api-wilayah-indonesia/" target="_blank" rel="noopener noreferrer">API Wilayah Indonesia — Emsifa</a>. Data tersimpan lokal setelah sinkron berhasil.</p>
    <div class="modal fade" id="modalSinkron" tabindex="-1" role="dialog" aria-labelledby="judulSinkron" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
            <div class="modal-header bg-primary"><h2 class="modal-title h5" id="judulSinkron">Sinkron Kota</h2></div>
            <div class="modal-body" id="isiSinkron" data-mulai="{{ route('kota.sinkron') }}" data-lanjut="{{ route('kota.lanjutkan') }}" data-daftar="{{ route('kota.daftar') }}" data-lanjut-otomatis="{{ ($proses['status'] ?? '') === 'berjalan' ? '1' : '0' }}" data-selesai="{{ request('sinkron') === 'selesai' ? '1' : '0' }}">
                @if ($proses) @include('kota.progres') @endif
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal" id="tutupSinkron" disabled>Tutup</button></div>
        </div></div>
    </div>
@endsection
@push('skrip')
<script>
    (function () {
        var tombol = document.getElementById('tombolSinkron');
        var isi = document.getElementById('isiSinkron');
        var tutup = document.getElementById('tutupSinkron');
        var berjalan = false;

        async function kirim(alamat, data) {
            var jawaban = await fetch(alamat, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'text/html'},
                body: new URLSearchParams(data)
            });
            if (!jawaban.ok || jawaban.redirected) {
                throw new Error('Proses terhenti. Muat ulang halaman untuk melanjutkan, atau login kembali jika sesi telah berakhir.');
            }
            isi.innerHTML = await jawaban.text();
            if (!isi.querySelector('#statusSinkron')) {
                throw new Error('Respons sinkron tidak valid. Silakan muat ulang halaman.');
            }
        }

        async function sinkron(lanjutkan) {
            if (berjalan) { return; }
            berjalan = true;
            tombol.disabled = true;
            tutup.disabled = true;
            $('#modalSinkron').modal('show');
            try {
                if (!lanjutkan) {
                    isi.innerHTML = '<p role="status"><i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i>Menghubungi API dan memuat daftar provinsi…</p>';
                    await kirim(isi.dataset.mulai, {});
                }
                var status = isi.querySelector('#statusSinkron');
                while (status.dataset.status === 'berjalan') {
                    await kirim(isi.dataset.lanjut, {id: status.dataset.id, langkah: status.dataset.langkah});
                    status = isi.querySelector('#statusSinkron');
                }
                if (status.dataset.status === 'berhasil') {
                    window.location.replace(isi.dataset.daftar + '?sinkron=selesai');
                    return;
                }
            } catch (kesalahan) {
                isi.innerHTML = '';
                var pesan = document.createElement('p');
                pesan.className = 'text-danger';
                pesan.setAttribute('role', 'alert');
                pesan.textContent = kesalahan.message || 'Koneksi terputus. Silakan muat ulang halaman untuk melanjutkan.';
                isi.appendChild(pesan);
            }
            berjalan = false;
            tombol.disabled = false;
            tutup.disabled = false;
        }
        tombol.addEventListener('click', function () { sinkron(false); });
        if (isi.dataset.lanjutOtomatis === '1') {
            sinkron(true);
        } else if (isi.dataset.selesai === '1' && isi.querySelector('#statusSinkron')) {
            tutup.disabled = false;
            $('#modalSinkron').modal('show');
        }
    })();
</script>
@endpush
