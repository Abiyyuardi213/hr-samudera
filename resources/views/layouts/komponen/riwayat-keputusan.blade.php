@if($pengajuan->riwayatKeputusan->isNotEmpty())
<details class="mt-2"><summary class="text-primary">Riwayat keputusan</summary><ul class="list-unstyled text-sm mb-0">@foreach($pengajuan->riwayatKeputusan as $riwayat)<li class="border-top py-1"><strong>{{ ucfirst($riwayat->status_lama) }} → {{ ucfirst($riwayat->status_baru) }}</strong><br>{{ $riwayat->nama_pemutus }} · {{ $riwayat->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB<br>{{ $riwayat->catatan }}</li>@endforeach</ul></details>
@endif
