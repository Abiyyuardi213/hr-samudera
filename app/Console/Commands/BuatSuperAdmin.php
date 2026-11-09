<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class BuatSuperAdmin extends Command
{
    protected $signature = 'pengguna:buat-admin';

    protected $description = 'Membuat akun super admin pertama secara interaktif';

    public function handle(): int
    {
        if (User::query()->whereHas('role', fn ($kueri) => $kueri->where('nama', 'super admin'))->where('status', true)->exists()) {
            $this->error('Super admin aktif sudah tersedia. Gunakan modul pengguna untuk menambah akun.');

            return self::FAILURE;
        }

        $nama = $this->ask('Nama super admin');
        $email = $this->ask('Email');
        $password = $this->secret('Password (minimal 8 karakter)');
        $konfirmasiPassword = $this->secret('Konfirmasi password');
        $validasi = Validator::make([
            'nama' => $nama, 'email' => $email, 'password' => $password, 'password_confirmation' => $konfirmasiPassword,
        ], [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], ['required' => ':attribute wajib diisi.', 'email' => 'Email tidak valid.', 'unique' => 'Email sudah digunakan.', 'min' => 'Password minimal 8 karakter.', 'confirmed' => 'Konfirmasi password tidak sesuai.']);

        if ($validasi->fails()) {
            foreach ($validasi->errors()->all() as $kesalahan) {
                $this->error($kesalahan);
            }

            return self::FAILURE;
        }

        $role = Role::query()->firstOrCreate(['nama' => 'super admin'], ['deskripsi' => 'Pengelola utama sistem HR perusahaan.', 'status' => true]);
        if (! $role->status) {
            $this->error('Role super admin tidak aktif. Aktifkan role sebelum membuat akun.');

            return self::FAILURE;
        }
        User::query()->create(['name' => $nama, 'email' => $email, 'password' => $password, 'role_id' => $role->id, 'status' => true]);
        $this->info('Super admin berhasil dibuat. Silakan masuk melalui halaman login.');

        return self::SUCCESS;
    }
}
