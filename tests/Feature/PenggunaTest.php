<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function pengelola(): User
    {
        $role = Role::query()->create(['nama' => 'super admin', 'status' => true]);

        return User::factory()->create(['role_id' => $role->id, 'status' => true]);
    }

    public function test_tamu_dan_staff_tidak_dapat_mengelola_pengguna(): void
    {
        $this->get('/administrator/users')->assertRedirect(route('login'));
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $pengguna = User::factory()->create(['role_id' => $role->id, 'status' => true]);
        $this->actingAs($pengguna)->get('/administrator/beranda')->assertOk();
        $this->get('/administrator/users')->assertForbidden();
        $this->post('/administrator/users', [])->assertForbidden();
        $this->get('/administrator/role')->assertForbidden();
    }

    public function test_crud_pengguna_dan_foto_profil(): void
    {
        Storage::fake('public');
        $admin = $this->pengelola();
        $this->actingAs($admin);
        $this->get('/administrator/users')->assertOk()->assertViewIs('pengguna.daftar');
        $this->get('/administrator/users/tambah')->assertOk();
        $data = ['nama' => 'Staf Baru', 'email' => 'staf@example.com', 'role_id' => $admin->role_id, 'status' => '1', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'];
        $this->post('/administrator/users', [...$data, 'foto_profil' => UploadedFile::fake()->image('profil.jpg')])->assertRedirect(route('pengguna.daftar'));
        $pengguna = User::query()->where('email', $data['email'])->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $pengguna->password));
        Storage::disk('public')->assertExists($pengguna->foto_profil);
        $fotoLama = $pengguna->foto_profil;
        $this->get('/administrator/users/'.$pengguna->id)->assertOk()->assertSee('Staf Baru');
        $this->get('/administrator/users/'.$pengguna->id.'/ubah')->assertOk();
        $this->put('/administrator/users/'.$pengguna->id, [...$data, 'nama' => 'Staf Diubah', 'password' => '', 'password_confirmation' => '', 'foto_profil' => UploadedFile::fake()->image('baru.png')])->assertRedirect(route('pengguna.daftar'));
        $pengguna->refresh();
        $this->assertTrue(Hash::check('rahasia123', $pengguna->password));
        Storage::disk('public')->assertMissing($fotoLama);
        Storage::disk('public')->assertExists($pengguna->foto_profil);
        $this->delete('/administrator/users/'.$pengguna->id)->assertRedirect(route('pengguna.daftar'));
        $this->assertDatabaseMissing('users', ['id' => $pengguna->id]);
        Storage::disk('public')->assertMissing($pengguna->foto_profil);
    }

    public function test_validasi_email_role_password_dan_foto(): void
    {
        $admin = $this->pengelola();
        $this->actingAs($admin)->post('/administrator/users', [
            'nama' => '', 'email' => $admin->email, 'role_id' => 'tidak-valid', 'status' => 'aktif',
            'password' => 'pendek', 'password_confirmation' => 'beda', 'foto_profil' => UploadedFile::fake()->create('berkas.pdf', 10),
        ])->assertSessionHasErrors(['nama', 'email', 'role_id', 'status', 'password', 'foto_profil']);
    }

    public function test_akun_sendiri_dan_role_yang_dipakai_dilindungi(): void
    {
        $admin = $this->pengelola();
        $this->actingAs($admin)->delete('/administrator/users/'.$admin->id)->assertSessionHasErrors('pengguna');
        $this->put('/administrator/users/'.$admin->id, ['nama' => $admin->name, 'email' => $admin->email, 'role_id' => $admin->role_id, 'status' => '0'])->assertSessionHasErrors('status');
        $this->delete('/administrator/role/'.$admin->role_id)->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => true]);
    }
}
