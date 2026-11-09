<div class="modal fade" id="modalKeputusan" tabindex="-1" aria-labelledby="judulKeputusan" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary"><h2 class="modal-title h5" id="judulKeputusan">Keputusan Pengajuan</h2><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button></div>
    <form method="POST" id="formulirKeputusan">@csrf
        <div class="modal-body"><p id="ringkasanKeputusan"></p><input type="hidden" name="keputusan" id="nilaiKeputusan">
            <label for="catatan_keputusan">Alasan keputusan / pembatalan</label><textarea name="catatan_keputusan" id="catatan_keputusan" class="form-control" required minlength="5" maxlength="2000">{{ old('catatan_keputusan') }}</textarea>
            <p class="text-muted mt-2 mb-0">Keputusan langsung berlaku. Pembatalan cuti yang disetujui akan mengembalikan saldo cuti.</p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary">Simpan keputusan</button></div>
    </form>
</div></div></div>
@push('skrip')
<script>
$('#modalKeputusan').on('show.bs.modal', function (kejadian) {
    var tombol = $(kejadian.relatedTarget);
    $('#formulirKeputusan').attr('action', tombol.attr('data-tujuan'));
    $('#nilaiKeputusan').val(tombol.attr('data-keputusan'));
    $('#ringkasanKeputusan').text(tombol.attr('data-ringkasan'));
});
</script>
@endpush
