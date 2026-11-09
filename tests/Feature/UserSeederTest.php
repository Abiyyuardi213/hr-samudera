<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dapat_login_dan_seeder_tidak_menggandakan_akun(): void
    {
        $this->seed(UserSeeder::class);
        $pengguna = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $this->assertTrue($pengguna->dapatMengelola());
        $this->assertTrue(Hash::check('password', $pengguna->password));
        $this->post('/administrator/login', ['email' => $pengguna->email, 'password' => 'password'])
            ->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs($pengguna);

        $pengguna->update(['password' => 'PasswordBaru123!']);
        $this->seed(UserSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check('PasswordBaru123!', $pengguna->refresh()->password));
    }
}
