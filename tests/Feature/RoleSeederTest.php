<?php

namespace Tests\Feature;

use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_lima_role_dan_aman_dijalankan_ulang(): void
    {
        $this->seed(RoleSeeder::class);
        $this->assertDatabaseCount('role', 5);

        foreach (['super admin', 'admin', 'staff HR', 'staff keuangan', 'staff umum'] as $namaRole) {
            $role = Role::query()->where('nama', $namaRole)->firstOrFail();
            $this->assertTrue(Str::isUuid($role->id));
            $this->assertTrue($role->status);
        }

        $role = Role::query()->where('nama', 'admin')->firstOrFail();
        $role->update(['deskripsi' => 'Deskripsi yang sudah disesuaikan.', 'status' => false]);

        $this->seed(RoleSeeder::class);
        $this->assertDatabaseCount('role', 5);
        $this->assertDatabaseHas('role', [
            'id' => $role->id,
            'deskripsi' => 'Deskripsi yang sudah disesuaikan.',
            'status' => false,
        ]);
    }
}
