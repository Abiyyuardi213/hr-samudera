@csrf
<div class="card-body">
    <div class="form-group">
        <label for="nama">Nama role <span class="text-danger" aria-hidden="true">*</span></label>
        <input class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $role->nama ?? '') }}" maxlength="255" placeholder="Contoh: Admin HR" required @error('nama') aria-invalid="true" aria-describedby="kesalahanNama" @enderror>
        @error('nama')<div class="invalid-feedback" id="kesalahanNama">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="deskripsi">Deskripsi <span class="text-muted font-weight-normal">(opsional)</span></label>
        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4" placeholder="Tuliskan keterangan role" @error('deskripsi') aria-invalid="true" aria-describedby="kesalahanDeskripsi" @enderror>{{ old('deskripsi', $role->deskripsi ?? '') }}</textarea>
        @error('deskripsi')<div class="invalid-feedback" id="kesalahanDeskripsi">{{ $message }}</div>@enderror
    </div>
    <div class="form-group mb-0">
        <label for="status">Status <span class="text-danger" aria-hidden="true">*</span></label>
        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required @error('status') aria-invalid="true" aria-describedby="kesalahanStatus" @enderror>
            <option value="1" @selected((string) old('status', isset($role) ? (int) $role->status : 1) === '1')>Aktif</option>
            <option value="0" @selected((string) old('status', isset($role) ? (int) $role->status : 1) === '0')>Nonaktif</option>
        </select>
        @error('status')<div class="invalid-feedback" id="kesalahanStatus">{{ $message }}</div>@enderror
    </div>
</div>
<div class="card-footer aksi">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan</button>
    <a href="{{ route('role.daftar') }}" class="btn btn-default">Kembali</a>
</div>

