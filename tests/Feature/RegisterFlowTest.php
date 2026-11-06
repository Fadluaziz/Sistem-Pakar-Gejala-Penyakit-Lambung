<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_register_diarahkan_ke_login(): void
    {
        // GET /register kini selalu me-redirect ke halaman login (pendaftaran
        // sudah menyatu sebagai panel "Daftar" di halaman login).
        $this->get(route('register'))
            ->assertRedirect(route('login'));
    }

    public function test_halaman_login_memuat_panel_daftar(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Daftar')
            ->assertSee('Nama Lengkap')
            ->assertSee('Konfirmasi Password');
    }

    public function test_pendaftaran_tetap_berfungsi_dan_otomatis_masuk(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Pasien Baru',
            'email' => 'pasien@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'pasien@example.com')->firstOrFail();
        $this->assertSame('patient', $user->role);

        // Pendaftaran mengarahkan ke dashboard per peran.
        $response->assertRedirect('/patient/dashboard');
    }

    public function test_pendaftaran_dengan_email_duplikat_ditolak(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->post(route('register.submit'), [
            'name' => 'Orang Lain',
            'email' => 'ada@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}