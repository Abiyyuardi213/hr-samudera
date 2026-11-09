<label for="{{ $identitas ?? 'pegawai_id' }}">Pegawai</label>
<select name="pegawai_id" id="{{ $identitas ?? 'pegawai_id' }}" class="form-control pilihan-cari" @if($wajib ?? false) required @endif>
    <option value="">{{ ($wajib ?? false) ? 'Pilih pegawai' : 'Semua pegawai' }}</option>
    @foreach($daftarPegawai as $pilihanPegawai)
        <option value="{{ $pilihanPegawai->id }}" @selected(($nilai ?? request('pegawai_id')) === $pilihanPegawai->id)>{{ $pilihanPegawai->nip }} — {{ $pilihanPegawai->nama_lengkap }} (saldo cuti: {{ $pilihanPegawai->sisa_cuti }} hari)</option>
    @endforeach
</select>
