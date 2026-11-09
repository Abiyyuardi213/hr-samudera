<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\ShiftPegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShiftPegawaiTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): void
    {
        $role = Role::query()->create(['nama' => 'admin', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
    }

    public function test_crud_shift_dan_durasi_tidak_dapat_diisi_manual(): void
    {
        $this->masuk();
        $this->get('/administrator/shift-pegawai')->assertOk()->assertSee('modalFormulirShift');
        $this->get('/administrator/shift-pegawai/tambah')->assertRedirect(route('shift-pegawai.daftar', ['tambah' => 1]));
        $data = ['nama_shift' => 'Shift Pagi', 'jam_masuk' => '08:00', 'jam_pulang' => '16:30', 'wajib_qr' => '1', 'durasi' => 999];
        $this->post('/administrator/shift-pegawai', $data)->assertRedirect(route('shift-pegawai.daftar'));
        $shift = ShiftPegawai::query()->firstOrFail();
        $this->assertTrue(Str::isUuid($shift->id));
        $this->assertSame(510, $shift->durasi);
        $this->assertTrue($shift->wajib_qr);
        $this->get('/administrator/shift-pegawai?cari=Pagi')->assertOk()->assertSee('8 jam 30 menit');
        $this->get('/administrator/shift-pegawai/'.$shift->id.'/ubah')->assertRedirect(route('shift-pegawai.daftar', ['ubah' => $shift->id]));
        $this->get('/administrator/shift-pegawai?ubah='.$shift->id)->assertOk()->assertSee('Ubah Shift')->assertSee('value="16:30"', false);
        $this->put('/administrator/shift-pegawai/'.$shift->id, [...$data, 'nama_shift' => 'Shift Malam', 'jam_masuk' => '22:00', 'jam_pulang' => '06:00', 'wajib_qr' => '0'])->assertRedirect(route('shift-pegawai.daftar'));
        $this->assertSame(480, $shift->refresh()->durasi);
        $this->assertFalse($shift->wajib_qr);
        $this->get('/administrator/shift-pegawai')->assertOk()->assertSee('Hari berikutnya')->assertSee('Tidak wajib');
        $this->delete('/administrator/shift-pegawai/'.$shift->id)->assertRedirect(route('shift-pegawai.daftar'));
        $this->assertDatabaseCount('shift_pegawai', 0);
    }

    public function test_perhitungan_durasi_menit_dan_pergantian_hari(): void
    {
        foreach ([['08:00', '16:00', 480], ['22:15', '06:45', 510], ['23:59', '00:00', 1], ['00:00', '23:59', 1439], ['08:00:00', '09:05:00', 65]] as [$jamMasuk, $jamPulang, $menit]) {
            $this->assertSame($menit, ShiftPegawai::hitungDurasi($jamMasuk, $jamPulang));
        }
    }

    public function test_validasi_jam_sama_format_salah_dan_qr(): void
    {
        $this->masuk();
        $this->post('/administrator/shift-pegawai', [])->assertSessionHasErrors(['nama_shift', 'jam_masuk', 'jam_pulang', 'wajib_qr']);
        $this->post('/administrator/shift-pegawai', ['nama_shift' => 'Shift', 'jam_masuk' => '08:00', 'jam_pulang' => '08:00', 'wajib_qr' => '1'])->assertSessionHasErrors('jam_pulang');
        $this->post('/administrator/shift-pegawai', ['nama_shift' => 'Shift', 'jam_masuk' => '25:00', 'jam_pulang' => 'salah', 'wajib_qr' => 'wajib'])->assertSessionHasErrors(['jam_masuk', 'jam_pulang', 'wajib_qr']);
        $this->assertDatabaseCount('shift_pegawai', 0);
    }

    public function test_durasi_diperbarui_saat_model_disimpan(): void
    {
        $shift = ShiftPegawai::query()->create(['nama_shift' => 'Shift', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00', 'wajib_qr' => false]);
        $shift->jam_pulang = '15:45';
        $shift->durasi = 999;
        $shift->save();
        $this->assertSame(525, $shift->refresh()->durasi);
    }

    public function test_tamu_dan_staff_tidak_dapat_mengelola_shift(): void
    {
        $this->get('/administrator/shift-pegawai')->assertRedirect(route('login'));
        $role = Role::query()->create(['nama' => 'staff HR', 'status' => true]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id, 'status' => true]));
        $this->get('/administrator/shift-pegawai')->assertForbidden();
        $this->post('/administrator/shift-pegawai', [])->assertForbidden();
    }
}
