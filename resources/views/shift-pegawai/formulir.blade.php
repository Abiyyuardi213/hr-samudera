<div class="modal fade" id="modalFormulirShift" tabindex="-1" role="dialog" aria-labelledby="judulFormulirShift" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
    <div class="modal-header bg-primary"><h2 class="modal-title h5" id="judulFormulirShift">{{ $shift ? 'Ubah' : 'Tambah' }} Shift</h2><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <form method="POST" action="{{ $shift ? route('shift-pegawai.perbarui', $shift) : route('shift-pegawai.simpan') }}">
        @csrf @if ($shift) @method('PUT') @endif
        <div class="modal-body">
            <div class="form-group">
                <label for="nama_shift">Nama shift</label>
                <input id="nama_shift" name="nama_shift" class="form-control @error('nama_shift') is-invalid @enderror" value="{{ old('nama_shift', $shift?->nama_shift) }}" maxlength="255" placeholder="Contoh: Shift Pagi" required>
                @error('nama_shift')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="jam_masuk">Jam masuk</label>
                    <input type="time" id="jam_masuk" name="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk', $shift ? substr($shift->jam_masuk, 0, 5) : '') }}" step="60" required>
                    @error('jam_masuk')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-sm-6 form-group">
                    <label for="jam_pulang">Jam pulang</label>
                    <input type="time" id="jam_pulang" name="jam_pulang" class="form-control @error('jam_pulang') is-invalid @enderror" value="{{ old('jam_pulang', $shift ? substr($shift->jam_pulang, 0, 5) : '') }}" step="60" required>
                    @error('jam_pulang')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-group">
                <label for="durasi">Durasi otomatis</label>
                <input id="durasi" class="form-control" placeholder="Isi jam masuk dan jam pulang" readonly aria-describedby="petunjukDurasi">
                <small class="text-muted" id="petunjukDurasi">Jika jam pulang lebih awal, shift berakhir pada hari berikutnya. Durasi belum dikurangi waktu istirahat.</small>
                <p id="keteranganDurasi" class="text-muted text-sm mt-1 mb-0" aria-live="polite"></p>
            </div>
            <div class="form-group mb-0">
                <label for="wajib_qr">Wajib QR</label>
                <select id="wajib_qr" name="wajib_qr" class="form-control @error('wajib_qr') is-invalid @enderror" required>
                    <option value="1" @selected((string) old('wajib_qr', $shift ? (int) $shift->wajib_qr : 0) === '1')>Wajib</option>
                    <option value="0" @selected((string) old('wajib_qr', $shift ? (int) $shift->wajib_qr : 0) === '0')>Tidak wajib</option>
                </select>
                @error('wajib_qr')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan</button></div>
    </form>
</div></div></div>
@push('skrip')
<script>
    (function () {
        var masuk = document.getElementById('jam_masuk');
        var pulang = document.getElementById('jam_pulang');
        var durasi = document.getElementById('durasi');
        var keterangan = document.getElementById('keteranganDurasi');
        function perbaruiDurasi() {
            durasi.value = '';
            keterangan.textContent = '';
            if (!masuk.value || !pulang.value) { return; }
            var bagianMasuk = masuk.value.split(':');
            var bagianPulang = pulang.value.split(':');
            var menitMasuk = Number(bagianMasuk[0]) * 60 + Number(bagianMasuk[1]);
            var menitPulang = Number(bagianPulang[0]) * 60 + Number(bagianPulang[1]);
            var selisih = (menitPulang - menitMasuk + 1440) % 1440;
            if (selisih === 0) {
                keterangan.textContent = 'Jam masuk dan jam pulang tidak boleh sama.';
                return;
            }
            durasi.value = Math.floor(selisih / 60) + ' jam' + (selisih % 60 ? ' ' + (selisih % 60) + ' menit' : '');
            if (menitPulang < menitMasuk) { keterangan.textContent = 'Jam pulang pada hari berikutnya.'; }
        }
        masuk.addEventListener('input', perbaruiDurasi);
        pulang.addEventListener('input', perbaruiDurasi);
        perbaruiDurasi();
    })();
</script>
@endpush
