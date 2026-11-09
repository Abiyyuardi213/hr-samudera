@csrf
<div class="card-body">
    @if ($daftarKota->isEmpty())
        <div class="alert alert-warning" role="alert">Data kota belum tersedia. <a href="{{ route('kota.daftar') }}">Sinkron kota terlebih dahulu</a> sebelum menyimpan kantor.</div>
    @endif
    <div class="form-group">
        <label for="kode_kantor">Kode kantor</label>
        <input id="kode_kantor" class="form-control" value="{{ $kantor->kode_kantor ?? '' }}" placeholder="Otomatis dibuat saat disimpan" readonly>
        <small class="text-muted">Kode unik dibuat dari nama kantor dan dipertahankan saat data diperbarui.</small>
    </div>
    <div class="form-group">
        <label for="nama_kantor">Nama kantor</label>
        <input id="nama_kantor" name="nama_kantor" class="form-control @error('nama_kantor') is-invalid @enderror" value="{{ old('nama_kantor', $kantor->nama_kantor ?? '') }}" placeholder="Contoh: Kantor Pusat" maxlength="255" required>
        @error('nama_kantor')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="kota_id">Kota</label>
        <select id="kota_id" name="kota_id" class="form-control @error('kota_id') is-invalid @enderror" required>
            <option value="">Pilih kabupaten/kota</option>
            @foreach ($daftarKota->groupBy('nama_provinsi') as $namaProvinsi => $kelompokKota)
                <optgroup label="{{ $namaProvinsi }}">
                    @foreach ($kelompokKota as $kota)
                        <option value="{{ $kota->id }}" @selected((string) old('kota_id', $kantor->kota_id ?? '') === (string) $kota->id)>{{ $kota->nama }} — {{ $kota->nama_provinsi }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('kota_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="alamat_lengkap">Alamat lengkap</label>
        <textarea id="alamat_lengkap" name="alamat_lengkap" class="form-control @error('alamat_lengkap') is-invalid @enderror" rows="3" maxlength="5000" required>{{ old('alamat_lengkap', $kantor->alamat_lengkap ?? '') }}</textarea>
        @error('alamat_lengkap')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="nomor_telepon">Nomor telepon</label>
            <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control @error('nomor_telepon') is-invalid @enderror" value="{{ old('nomor_telepon', $kantor->nomor_telepon ?? '') }}" maxlength="30" placeholder="Contoh: +62 21 1234567" required>
            @error('nomor_telepon')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $kantor->email ?? '') }}" maxlength="255" required>
            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
<div class="card-footer aksi">
    <button type="submit" class="btn btn-primary" @disabled($daftarKota->isEmpty())><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan</button>
    @if ($dalamModal ?? false)
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
    @else
        <a href="{{ route('kantor.daftar') }}" class="btn btn-default">Kembali</a>
    @endif
</div>
@push('gaya')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush
@push('skrip')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    if ($.fn.select2) {
        $('#kota_id').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Ketik nama kota atau provinsi',
            dropdownParent: $('#kota_id').closest('.modal').length ? $('#kota_id').closest('.modal') : $(document.body),
            language: { noResults: function () { return 'Kota tidak ditemukan'; }, searching: function () { return 'Mencari kota…'; } }
        });
    }
</script>
@endpush
