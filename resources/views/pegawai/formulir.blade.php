@php($masterSiap = $daftarJabatan->isNotEmpty() && $daftarKantor->isNotEmpty() && $daftarStatus->isNotEmpty() && $daftarShift->isNotEmpty())
<div class="modal fade" id="modalFormulirPegawai" tabindex="-1" role="dialog" aria-labelledby="judulFormulirPegawai" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content">
    <div class="modal-header bg-primary"><h2 class="modal-title h5" id="judulFormulirPegawai">{{ $pegawai ? 'Ubah' : 'Tambah' }} Pegawai</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" class="d-flex flex-column" style="min-height: 0;" action="{{ $pegawai ? route('pegawai.perbarui', $pegawai) : route('pegawai.simpan') }}">
        @csrf @if ($pegawai) @method('PUT') @endif
        <div class="modal-body">
            @unless ($masterSiap)
                <div class="alert alert-warning" role="alert">Lengkapi data master sebelum menambahkan pegawai:
                    @if ($daftarJabatan->isEmpty())<a class="d-block" href="{{ route('jabatan.tambah') }}">Tambah jabatan dan unit organisasi</a>@endif
                    @if ($daftarKantor->isEmpty())<a class="d-block" href="{{ route('kantor.tambah') }}">Tambah kantor</a>@endif
                    @if ($daftarStatus->isEmpty())<a class="d-block" href="{{ route('status-pegawai.tambah') }}">Tambah status pegawai</a>@endif
                    @if ($daftarShift->isEmpty())<a class="d-block" href="{{ route('shift-pegawai.tambah') }}">Tambah shift kerja</a>@endif
                </div>
            @endunless
            <h3 class="h6 font-weight-bold border-bottom pb-2">Identitas Pegawai</h3>
            <div class="row">
                @foreach (['nama_lengkap' => ['Nama lengkap', 'text', 255], 'nip' => ['NIP', 'text', 50], 'nik' => ['NIK', 'text', 16], 'tempat_lahir' => ['Tempat lahir', 'text', 255]] as $atribut => $pengaturan)
                    <div class="col-md-6 form-group"><label for="{{ $atribut }}">{{ $pengaturan[0] }}</label><input id="{{ $atribut }}" name="{{ $atribut }}" type="{{ $pengaturan[1] }}" class="form-control @error($atribut) is-invalid @enderror" value="{{ old($atribut, $pegawai?->{$atribut}) }}" maxlength="{{ $pengaturan[2] }}" @if ($atribut === 'nik') inputmode="numeric" pattern="[0-9]{16}" @endif required>@error($atribut)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                @endforeach
                <div class="col-md-6 form-group"><label for="tanggal_lahir">Tanggal lahir</label><input id="tanggal_lahir" name="tanggal_lahir" type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $pegawai?->tanggal_lahir?->format('Y-m-d')) }}" required>@error('tanggal_lahir')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="col-md-6 form-group"><label for="jenis_kelamin">Jenis kelamin</label><select id="jenis_kelamin" name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required><option value="">Pilih jenis kelamin</option><option value="laki_laki" @selected(old('jenis_kelamin', $pegawai?->jenis_kelamin) === 'laki_laki')>Laki-laki</option><option value="perempuan" @selected(old('jenis_kelamin', $pegawai?->jenis_kelamin) === 'perempuan')>Perempuan</option></select>@error('jenis_kelamin')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="col-12 form-group"><label for="alamat_domisili">Alamat domisili</label><textarea id="alamat_domisili" name="alamat_domisili" class="form-control @error('alamat_domisili') is-invalid @enderror" rows="2" maxlength="5000" required>{{ old('alamat_domisili', $pegawai?->alamat_domisili) }}</textarea>@error('alamat_domisili')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            </div>
            <h3 class="h6 font-weight-bold border-bottom pb-2">Penempatan dan Pekerjaan</h3>
            @if ($pegawai)
                <div class="alert alert-info text-sm">Perubahan jabatan, divisi, kantor, status, dan shift dicatat melalui <a href="{{ route('penempatan-pegawai.daftar', ['pegawai_id' => $pegawai->id, 'tambah' => 1]) }}">Penempatan &amp; Riwayat</a> agar riwayat tetap tersimpan.</div>
                @foreach (['jabatan_id', 'kantor_id', 'status_pegawai_id', 'shift_pegawai_id'] as $kolomPenempatan)
                    <input type="hidden" name="{{ $kolomPenempatan }}" value="{{ $pegawai->{$kolomPenempatan} }}">
                @endforeach
            @endif
            <div class="row">
                <div class="col-md-6 form-group"><label for="jabatan_id">Jabatan</label><select id="jabatan_id" name="jabatan_id" class="form-control pilihan-master @error('jabatan_id') is-invalid @enderror" @disabled((bool) $pegawai) required><option value="">Pilih jabatan</option>@foreach ($daftarJabatan as $jabatan)<option value="{{ $jabatan->id }}" data-divisi="{{ $petaDivisi->get($jabatan->id)?->nama ?? 'Tanpa divisi' }}" @selected(old('jabatan_id', $pegawai?->jabatan_id) === $jabatan->id)>{{ $jabatan->nama }} — {{ $jabatan->unitOrganisasi->nama }}</option>@endforeach</select>@error('jabatan_id')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="col-md-6 form-group"><label for="divisi">Divisi (otomatis)</label><input id="divisi" class="form-control" readonly placeholder="Pilih jabatan terlebih dahulu"><small class="text-muted">Diambil dari unit organisasi jabatan atau induknya yang berjenis divisi.</small></div>
                @foreach (['kantor_id' => ['Kantor', $daftarKantor, 'nama_kantor'], 'status_pegawai_id' => ['Status pegawai', $daftarStatus, 'nama'], 'shift_pegawai_id' => ['Shift kerja', $daftarShift, 'nama_shift']] as $atribut => $pengaturan)
                    <div class="col-md-6 form-group"><label for="{{ $atribut }}">{{ $pengaturan[0] }}</label><select id="{{ $atribut }}" name="{{ $atribut }}" class="form-control pilihan-master @error($atribut) is-invalid @enderror" @disabled((bool) $pegawai) required><option value="">Pilih {{ strtolower($pengaturan[0]) }}</option>@foreach ($pengaturan[1] as $pilihan)<option value="{{ $pilihan->id }}" @selected(old($atribut, $pegawai?->{$atribut}) === $pilihan->id)>{{ $pilihan->{$pengaturan[2]} }}@if ($atribut === 'shift_pegawai_id') ({{ substr($pilihan->jam_masuk, 0, 5) }}–{{ substr($pilihan->jam_pulang, 0, 5) }})@endif</option>@endforeach</select>@error($atribut)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                @endforeach
                <div class="col-md-6 form-group"><label for="tanggal_masuk">Tanggal masuk</label><input id="tanggal_masuk" name="tanggal_masuk" type="date" @readonly((bool) $pegawai) class="form-control @error('tanggal_masuk') is-invalid @enderror" value="{{ old('tanggal_masuk', $pegawai?->tanggal_masuk?->format('Y-m-d')) }}" required>@error('tanggal_masuk')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="col-md-6 form-group"><label for="sisa_cuti">Sisa cuti (hari)</label><input id="sisa_cuti" name="sisa_cuti" type="number" @readonly($pegawai && $pegawai->ketidakhadiran()->where('jenis', 'cuti')->exists()) class="form-control @error('sisa_cuti') is-invalid @enderror" value="{{ old('sisa_cuti', $pegawai?->sisa_cuti ?? 0) }}" min="0" max="365" step="1" required>@error('sisa_cuti')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            </div>
            <h3 class="h6 font-weight-bold border-bottom pb-2">Kontak</h3>
            <div class="row">
                <div class="col-md-6 form-group"><label for="nomor_telepon">Nomor telepon</label><input id="nomor_telepon" name="nomor_telepon" type="tel" class="form-control @error('nomor_telepon') is-invalid @enderror" value="{{ old('nomor_telepon', $pegawai?->nomor_telepon) }}" maxlength="30" required>@error('nomor_telepon')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="col-md-6 form-group"><label for="email">Email</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $pegawai?->email) }}" maxlength="255" required>@error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" @disabled(!$masterSiap)><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan</button></div>
    </form>
</div></div></div>
@push('gaya')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush
@push('skrip')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    if ($.fn.select2) {
        $('.pilihan-master').select2({theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalFormulirPegawai'), language: {noResults: function () { return 'Data tidak ditemukan'; }}});
    }
    function perbaruiDivisi() {
        var pilihan = document.getElementById('jabatan_id').selectedOptions[0];
        document.getElementById('divisi').value = pilihan && pilihan.value ? pilihan.getAttribute('data-divisi') : '';
    }
    $('#jabatan_id').on('change', perbaruiDivisi);
    perbaruiDivisi();
</script>
@endpush

