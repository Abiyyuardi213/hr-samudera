<div class="modal fade" id="modalPenempatan" tabindex="-1" role="dialog" aria-labelledby="judulPenempatan" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content">
    <div class="modal-header bg-primary"><h2 class="modal-title h5" id="judulPenempatan">Catat Penempatan Pegawai</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" class="d-flex flex-column" style="min-height: 0;" action="{{ route('penempatan-pegawai.simpan') }}">
        @csrf
        <input type="hidden" id="versi_penempatan" name="versi_penempatan" value="{{ old('versi_penempatan', $pegawaiDipilih?->riwayatPenempatan()->orderByDesc('tanggal_mulai')->value('id')) }}">
        <div class="modal-body">
            @if ($daftarPegawai->isEmpty())<div class="alert alert-warning">Belum ada pegawai. <a href="{{ route('pegawai.tambah') }}">Tambah pegawai terlebih dahulu.</a></div>@endif
            <div class="form-group"><label for="pegawai_id">Pegawai</label><select id="pegawai_id" name="pegawai_id" class="form-control pilihan-penempatan" required><option value="">Pilih pegawai</option>
                @foreach ($daftarPegawai as $pilihan)
                    @php($terakhir = $pilihan->riwayatPenempatan->first())
                    <option value="{{ $pilihan->id }}" data-versi="{{ $terakhir?->id }}" data-jabatan="{{ $pilihan->jabatan_id }}" data-kantor="{{ $pilihan->kantor_id }}" data-status="{{ $pilihan->status_pegawai_id }}" data-shift="{{ $pilihan->shift_pegawai_id }}" data-ringkasan="{{ $pilihan->jabatan->nama }} — {{ $pilihan->kantor->nama_kantor }}. Terakhir berlaku: {{ $terakhir?->tanggal_mulai?->format('d/m/Y') ?? $pilihan->tanggal_masuk->format('d/m/Y') }}" @selected(old('pegawai_id', $pegawaiDipilih?->id) === $pilihan->id)>{{ $pilihan->nip }} — {{ $pilihan->nama_lengkap }}</option>
                @endforeach
            </select>@error('pegawai_id')<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
            <div class="alert alert-light border text-sm" id="ringkasanPenempatan" role="status">Pilih pegawai untuk melihat penempatan saat ini.</div>
            <div class="row">
                <div class="col-md-6 form-group"><label for="jenis">Jenis perubahan</label><select id="jenis" name="jenis" class="form-control" required><option value="">Pilih jenis perubahan</option>@foreach ($jenisPenempatan as $nilai => $label) @if ($nilai !== 'awal')<option value="{{ $nilai }}" @selected(old('jenis') === $nilai)>{{ $label }}</option>@endif @endforeach</select>@error('jenis')<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
                <div class="col-md-6 form-group"><label for="tanggal_mulai">Tanggal berlaku</label><input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', now('Asia/Jakarta')->toDateString()) }}" max="{{ now('Asia/Jakarta')->toDateString() }}" required>@error('tanggal_mulai')<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
            </div>
            <p class="text-muted text-sm">Tanggal berlaku harus setelah penempatan terakhir, maksimal hari ini. Periode sebelumnya ditutup sehari sebelum tanggal ini.</p>
            <div class="form-group"><label for="jabatan_id">Jabatan tujuan</label><select id="jabatan_id" name="jabatan_id" class="form-control pilihan-penempatan" required><option value="">Pilih jabatan</option>@foreach ($daftarJabatan as $jabatan)<option value="{{ $jabatan->id }}" data-divisi="{{ $petaDivisi->get($jabatan->id)?->nama ?? 'Tanpa divisi' }}" @selected(old('jabatan_id', $pegawaiDipilih?->jabatan_id) === $jabatan->id)>{{ $jabatan->nama }} — {{ $jabatan->unitOrganisasi->nama }}</option>@endforeach</select>@error('jabatan_id')<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label for="divisiPenempatan">Divisi tujuan (otomatis)</label><input id="divisiPenempatan" class="form-control" readonly placeholder="Pilih jabatan terlebih dahulu"></div>
            <div class="row">
                @foreach (['kantor_id' => ['Kantor tujuan', $daftarKantor, 'nama_kantor'], 'status_pegawai_id' => ['Status pegawai', $daftarStatus, 'nama'], 'shift_pegawai_id' => ['Shift kerja', $daftarShift, 'nama_shift']] as $atribut => $pengaturan)
                    <div class="col-md-6 form-group"><label for="{{ $atribut }}">{{ $pengaturan[0] }}</label><select id="{{ $atribut }}" name="{{ $atribut }}" class="form-control pilihan-penempatan" required><option value="">Pilih {{ strtolower($pengaturan[0]) }}</option>@foreach ($pengaturan[1] as $pilihan)<option value="{{ $pilihan->id }}" @selected(old($atribut, $pegawaiDipilih?->{$atribut}) === $pilihan->id)>{{ $pilihan->{$pengaturan[2]} }}@if ($atribut === 'shift_pegawai_id') ({{ substr($pilihan->jam_masuk, 0, 5) }}–{{ substr($pilihan->jam_pulang, 0, 5) }})@endif</option>@endforeach</select>@error($atribut)<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
                @endforeach
                <div class="col-md-6 form-group"><label for="nomor_sk">Nomor SK (opsional)</label><input id="nomor_sk" name="nomor_sk" class="form-control" value="{{ old('nomor_sk') }}" maxlength="100">@error('nomor_sk')<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
            </div>
            <div class="form-group mb-0"><label for="keterangan">Catatan / alasan perubahan</label><textarea id="keterangan" name="keterangan" class="form-control" rows="3" maxlength="5000" required>{{ old('keterangan') }}</textarea>@error('keterangan')<div class="text-danger text-sm">{{ $message }}</div>@enderror</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" @disabled($daftarPegawai->isEmpty())><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan penempatan</button></div>
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
        $('.pilihan-penempatan').select2({theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalPenempatan'), language: {noResults: function () { return 'Data tidak ditemukan'; }}});
        $('#filterPegawai').select2({theme: 'bootstrap4', width: '100%'});
    }
    function perbaruiDivisiPenempatan() {
        var pilihan = document.getElementById('jabatan_id').selectedOptions[0];
        document.getElementById('divisiPenempatan').value = pilihan && pilihan.value ? pilihan.getAttribute('data-divisi') : '';
    }
    function pilihPegawaiPenempatan(isiTujuan) {
        var pilihan = document.getElementById('pegawai_id').selectedOptions[0];
        document.getElementById('ringkasanPenempatan').textContent = pilihan && pilihan.value ? pilihan.getAttribute('data-ringkasan') : 'Pilih pegawai untuk melihat penempatan saat ini.';
        if (isiTujuan) {
            document.getElementById('versi_penempatan').value = pilihan ? pilihan.getAttribute('data-versi') || '' : '';
            $('#jabatan_id').val(pilihan ? pilihan.getAttribute('data-jabatan') : '').trigger('change');
            $('#kantor_id').val(pilihan ? pilihan.getAttribute('data-kantor') : '').trigger('change');
            $('#status_pegawai_id').val(pilihan ? pilihan.getAttribute('data-status') : '').trigger('change');
            $('#shift_pegawai_id').val(pilihan ? pilihan.getAttribute('data-shift') : '').trigger('change');
        }
    }
    $('#pegawai_id').on('change', function () { pilihPegawaiPenempatan(true); });
    $('#jabatan_id').on('change', perbaruiDivisiPenempatan);
    pilihPegawaiPenempatan(false);
    perbaruiDivisiPenempatan();
</script>
@endpush
