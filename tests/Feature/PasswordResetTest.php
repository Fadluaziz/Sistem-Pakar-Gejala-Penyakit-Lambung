<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_lupa_password_dapat_dibuka(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Password');
    }

    public function test_tautan_reset_dikirim_ke_email_terdaftar(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'patient']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_email_tidak_terdaftar_menampilkan_error(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_email_wajib_valid(): void
    {
        $this->post(route('password.email'), ['email' => 'bukan-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_pasien_dapat_membuat_password_baru_dengan_token(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'pasien@example.com',
        ]);

        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordbaru123', $user->fresh()->password));
    }

    public function test_token_salah_ditolak(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'email' => 'pasien2@example.com',
        ]);

        $this->post(route('password.store'), [
            'token' => 'token-palsu',
            'email' => $user->email,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertSessionHasErrors('email');
    }

    public function test_formulir_reset_menampilkan_email_dari_query(): void
    {
        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'a@b.test']))
            ->assertOk()
            ->assertSee('Buat Password Baru')
            ->assertSee('a@b.test');
    }

    public function test_halaman_login_memuat_tautan_lupa_password(): void
    {
        $this->get(route('login'))->assertSee('Lupa password?');
    }
}
