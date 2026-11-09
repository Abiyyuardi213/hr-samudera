<div class="card-footer">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <span class="text-muted text-sm">{{ $halaman->total() }} {{ $satuan ?? 'catatan' }} &middot; Halaman {{ $halaman->currentPage() }} dari {{ $halaman->lastPage() }}</span>
        <nav aria-label="{{ $labelNavigasi ?? 'Navigasi halaman data' }}" class="ml-auto mt-2 mt-sm-0">
            <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-end">
                <li class="page-item {{ $halaman->onFirstPage() ? 'disabled' : '' }}" @if($halaman->onFirstPage()) aria-disabled="true" @endif>
                    @if($halaman->previousPageUrl())<a class="page-link" href="{{ $halaman->previousPageUrl() }}" rel="prev">Sebelumnya</a>@else<span class="page-link">Sebelumnya</span>@endif
                </li>
                @php
                    $halamanTengah = min($halaman->currentPage(), $halaman->lastPage());
                    $nomorHalaman = array_values(array_unique(array_merge(
                        [1],
                        range(max(1, $halamanTengah - 2), min($halaman->lastPage(), $halamanTengah + 2)),
                        [$halaman->lastPage()]
                    )));
                    $nomorSebelumnya = 0;
                @endphp
                @foreach($nomorHalaman as $nomor)
                    @if($nomor > $nomorSebelumnya + 1)
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">&hellip;</span></li>
                    @endif
                    @if($nomor === $halaman->currentPage())
                        <li class="page-item active" aria-current="page"><span class="page-link" aria-label="Halaman {{ $nomor }}, halaman saat ini">{{ $nomor }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $halaman->url($nomor) }}" aria-label="Ke halaman {{ $nomor }}">{{ $nomor }}</a></li>
                    @endif
                    @php($nomorSebelumnya = $nomor)
                @endforeach
                <li class="page-item {{ $halaman->hasMorePages() ? '' : 'disabled' }}" @unless($halaman->hasMorePages()) aria-disabled="true" @endunless>
                    @if($halaman->nextPageUrl())<a class="page-link" href="{{ $halaman->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span class="page-link">Berikutnya</span>@endif
                </li>
            </ul>
        </nav>
    </div>
</div>
