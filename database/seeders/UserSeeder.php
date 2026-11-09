<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->firstOrCreate(
            ['nama' => 'super admin'],
            ['deskripsi' => 'Pengelola utama sistem HR perusahaan.', 'status' => true],
        );

        $penggunaLama = User::query()->where('email', 'superadmin@samudera.test')->first();

        if ($penggunaLama && ! User::query()->where('email', 'admin@gmail.com')->exists()) {
            $penggunaLama->update(['email' => 'admin@gmail.com', 'password' => 'password']);
        }

        User::query()->firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role_id' => $role->id,
                'status' => true,
            ],
        );
    }
}
