@php($persentase = $proses['total'] > 0 ? (int) floor($proses['selesai'] / $proses['total'] * 100) : 0)
<div id="statusSinkron" data-status="{{ $proses['status'] }}" data-id="{{ $proses['id'] }}" data-langkah="{{ $proses['selesai'] }}">
    <p role="status" aria-live="polite" class="{{ $proses['status'] === 'gagal' ? 'text-danger' : '' }}">{{ $proses['pesan'] }}</p>
    <div class="progress mb-3" style="height: 24px;">
        <div class="progress-bar {{ $proses['status'] === 'berjalan' ? 'progress-bar-striped progress-bar-animated' : ($proses['status'] === 'gagal' ? 'bg-danger' : 'bg-success') }}" role="progressbar" style="width: {{ $persentase }}%;" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100">{{ $persentase }}%</div>
    </div>
    <p class="text-muted mb-0">{{ $proses['selesai'] }} dari {{ $proses['total'] }} provinsi diproses &middot; {{ $proses['jumlah'] }} kabupaten/kota diambil.</p>
</div>
