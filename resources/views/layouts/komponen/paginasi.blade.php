<div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
    <span class="text-muted">{{ $halaman->total() }} catatan · Halaman {{ $halaman->currentPage() }} dari {{ $halaman->lastPage() }}</span>
    <nav aria-label="Halaman data"><ul class="pagination pagination-sm mb-0">
        <li class="page-item {{ $halaman->previousPageUrl() ? '' : 'disabled' }}"><a class="page-link" href="{{ $halaman->previousPageUrl() ?? '#' }}">Sebelumnya</a></li>
        <li class="page-item {{ $halaman->nextPageUrl() ? '' : 'disabled' }}"><a class="page-link" href="{{ $halaman->nextPageUrl() ?? '#' }}">Berikutnya</a></li>
    </ul></nav>
</div>
