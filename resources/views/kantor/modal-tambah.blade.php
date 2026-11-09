<div class="modal fade" id="modalTambahKantor" tabindex="-1" role="dialog" aria-labelledby="judulTambahKantor" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h2 class="modal-title h5" id="judulTambahKantor">Tambah Kantor</h2>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <form method="POST" action="{{ route('kantor.simpan') }}">
                    @include('kantor.formulir', ['kantor' => null, 'dalamModal' => true])
                </form>
            </div>
        </div>
    </div>
</div>
