<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::query()->create(['nama' => 'super admin']);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
    }

    public function test_role_dapat_dikelola_melalui_formulir_blade(): void
    {
        $this->get('/administrator/role')->assertOk()->assertViewIs('role.daftar');
        $this->get('/administrator/role/tambah')->assertOk()->assertViewIs('role.tambah');
        $this->post('/administrator/role', ['nama' => 'Nakhoda', 'deskripsi' => 'Pemimpin kapal', 'status' => '1'])
            ->assertRedirect(route('role.daftar'))->assertSessionHas('pesan', 'Role berhasil ditambahkan.');

        $role = Role::query()->where('nama', 'Nakhoda')->firstOrFail();
        $this->assertTrue(Str::isUuid($role->id));
        $this->get('/administrator/role')->assertOk()->assertViewHas('daftarRole')->assertSee('Nakhoda');
        $this->get('/administrator/role/'.$role->id)->assertOk()->assertViewHas('role')->assertSee('Pemimpin kapal');
        $this->get('/administrator/role/'.$role->id.'/ubah')->assertOk()->assertSee('Nakhoda');
        $this->put('/administrator/role/'.$role->id, ['nama' => 'Admin HR', 'deskripsi' => '', 'status' => '0'])
            ->assertRedirect(route('role.daftar'))->assertSessionHas('pesan', 'Role berhasil diperbarui.');
        $this->assertDatabaseHas('role', ['id' => $role->id, 'nama' => 'Admin HR', 'deskripsi' => null, 'status' => false]);
        $this->get('/administrator/role/'.$role->id.'/ubah')->assertOk()->assertSee('value="0" selected', false);
        $this->delete('/administrator/role/'.$role->id)->assertRedirect(route('role.daftar'));
        $this->assertDatabaseMissing('role', ['id' => $role->id]);
        $this->get('/administrator/role/'.$role->id)->assertNotFound();
    }

    public function test_validasi_mengembalikan_kesalahan_dan_isian_formulir(): void
    {
        $this->from('/administrator/role/tambah')->post('/administrator/role', ['nama' => '', 'status' => 'aktif'])
            ->assertRedirect('/administrator/role/tambah')->assertSessionHasErrors(['nama', 'status']);
        $this->from('/administrator/role/tambah')->post('/administrator/role', ['nama' => 'Nakhoda', 'deskripsi' => [], 'status' => '1'])
            ->assertSessionHasErrors('deskripsi')->assertSessionHasInput('nama', 'Nakhoda');
        $this->assertDatabaseCount('role', 1);
        $this->get('/administrator/role/bukan-uuid')->assertNotFound();
    }

    public function test_teks_role_ditampilkan_dengan_aman(): void
    {
        $role = Role::query()->create(['nama' => '<script>alert(1)</script>']);
        $this->get('/administrator/role')->assertOk()->assertSee($role->nama)->assertDontSee($role->nama, false);
    }
}
