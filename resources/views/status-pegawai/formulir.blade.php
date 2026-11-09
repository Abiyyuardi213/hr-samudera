<div class="modal fade" id="modalFormulirStatus" tabindex="-1" role="dialog" aria-labelledby="judulFormulirStatus" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
    <div class="modal-header bg-primary"><h2 class="modal-title h5" id="judulFormulirStatus">{{ $statusPegawai ? 'Ubah' : 'Tambah' }} Status Pegawai</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" action="{{ $statusPegawai ? route('status-pegawai.perbarui', $statusPegawai) : route('status-pegawai.simpan') }}">
        @csrf @if ($statusPegawai) @method('PUT') @endif
        <div class="modal-body">
            <div class="form-group"><label for="kode">Kode</label><input id="kode" class="form-control" value="{{ $statusPegawai?->kode }}" placeholder="Otomatis dibuat saat disimpan" readonly><small class="text-muted">Kode unik dibuat dari nama status dan tetap saat data diperbarui.</small></div>
            <div class="form-group"><label for="nama">Nama status</label><input id="nama" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $statusPegawai?->nama) }}" maxlength="255" placeholder="Contoh: Pegawai Tetap" required>@error('nama')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="form-group mb-0"><label for="deskripsi">Deskripsi (opsional)</label><textarea id="deskripsi" name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" maxlength="5000">{{ old('deskripsi', $statusPegawai?->deskripsi) }}</textarea>@error('deskripsi')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan</button></div>
    </form>
</div></div></div>
