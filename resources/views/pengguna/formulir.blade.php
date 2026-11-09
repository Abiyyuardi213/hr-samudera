@csrf
<div class="card-body">
    <div class="form-group">
        <label for="nama">Nama</label>
        <input id="nama" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $pengguna->name ?? '') }}" maxlength="255" required>
        @error('nama')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $pengguna->email ?? '') }}" maxlength="255" required autocomplete="off">
        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="role_id">Role</label>
            <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                <option value="">Pilih role</option>
                @foreach ($daftarRole as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id', $pengguna->role_id ?? '') === $role->id)>{{ $role->nama }}{{ $role->status ? '' : ' (nonaktif)' }}</option>
                @endforeach
            </select>
            @error('role_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                <option value="1" @selected((string) old('status', isset($pengguna) ? (int) $pengguna->status : 1) === '1')>Aktif</option>
                <option value="0" @selected((string) old('status', isset($pengguna) ? (int) $pengguna->status : 1) === '0')>Nonaktif</option>
            </select>
            @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" @required(!isset($pengguna))>
            <small class="text-muted">Minimal 8 karakter. @isset($pengguna) Kosongkan jika tidak diganti. @endisset</small>
            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="password_confirmation">Konfirmasi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password" @required(!isset($pengguna))>
        </div>
    </div>
    <div class="form-group">
        <label for="foto_profil">Foto profil (opsional)</label>
        @if (isset($pengguna) && $pengguna->foto_profil)
            <div class="mb-2"><img src="{{ asset('storage/'.$pengguna->foto_profil) }}" alt="Foto profil {{ $pengguna->name }}" width="80" height="80" class="img-circle" style="object-fit: cover;"></div>
        @endif
        <input type="file" id="foto_profil" name="foto_profil" class="form-control-file @error('foto_profil') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" aria-describedby="petunjukFoto">
        <small id="petunjukFoto" class="text-muted">JPG, PNG, atau WebP. Maksimal 2 MB.</small>
        @error('foto_profil')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
</div>
<div class="card-footer aksi">
    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan</button>
    <a href="{{ route('pengguna.daftar') }}" class="btn btn-default">Kembali</a>
</div>
