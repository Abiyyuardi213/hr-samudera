<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_super_admin_pertama_dapat_dibuat_dari_terminal(): void
    {
        $this->artisan('pengguna:buat-admin')
            ->expectsQuestion('Nama super admin', 'Pengelola HR')
            ->expectsQuestion('Email', 'pengelola@example.com')
            ->expectsQuestion('Password (minimal 8 karakter)', 'rahasia123')
            ->expectsQuestion('Konfirmasi password', 'rahasia123')
            ->assertSuccessful();

        $pengguna = User::query()->where('email', 'pengelola@example.com')->firstOrFail();
        $this->assertTrue($pengguna->dapatMengelola());
        $this->artisan('pengguna:buat-admin')->assertFailed();
    }

    public function test_login_dan_logout_dengan_email_password(): void
    {
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $pengguna = User::factory()->create(['email' => 'hr@example.com', 'password' => 'rahasia123', 'role_id' => $role->id, 'status' => true]);
        $this->get('/administrator/login')->assertOk();
        $this->post('/administrator/login', ['email' => $pengguna->email, 'password' => 'rahasia123'])->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs($pengguna);
        $this->get('/administrator/beranda')->assertOk()->assertSee($pengguna->name);
        $this->post('/administrator/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_salah_akun_nonaktif_dan_role_nonaktif_ditolak(): void
    {
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $pengguna = User::factory()->create(['password' => 'rahasia123', 'role_id' => $role->id, 'status' => false]);
        $this->post('/administrator/login', ['email' => $pengguna->email, 'password' => 'rahasia123'])->assertSessionHasErrors('email');
        $pengguna->update(['status' => true]);
        $this->post('/administrator/login', ['email' => $pengguna->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $role->update(['status' => false]);
        $this->post('/administrator/login', ['email' => $pengguna->email, 'password' => 'rahasia123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_percobaan_login_dibatasi(): void
    {
        for ($percobaan = 0; $percobaan < 5; $percobaan++) {
            $this->post('/administrator/login', ['email' => 'salah@example.com', 'password' => 'salah'])->assertSessionHasErrors('email');
        }
        $this->post('/administrator/login', ['email' => 'salah@example.com', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
    }

    public function test_akun_yang_dinonaktifkan_dikeluarkan_dari_session(): void
    {
        $pengguna = User::factory()->create(['status' => false]);
        $this->actingAs($pengguna)->get('/administrator/beranda')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
