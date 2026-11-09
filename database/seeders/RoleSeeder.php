<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $daftarRole = [
            ['nama' => 'super admin', 'deskripsi' => 'Pengelola utama sistem HR perusahaan.'],
            ['nama' => 'admin', 'deskripsi' => 'Pengelola administrasi sistem HR perusahaan.'],
            ['nama' => 'staff HR', 'deskripsi' => 'Staf yang menangani administrasi sumber daya manusia.'],
            ['nama' => 'staff keuangan', 'deskripsi' => 'Staf yang menangani administrasi keuangan perusahaan.'],
            ['nama' => 'staff umum', 'deskripsi' => 'Staf yang menangani administrasi umum perusahaan.'],
        ];

        foreach ($daftarRole as $dataRole) {
            Role::query()->firstOrCreate(
                ['nama' => $dataRole['nama']],
                ['deskripsi' => $dataRole['deskripsi'], 'status' => true],
            );
        }
    }
}
