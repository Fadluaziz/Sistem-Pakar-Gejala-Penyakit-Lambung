<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'patient'): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => Hash::make('passwordlama'),
        ]);
    }

    public function test_pengguna_login_bisa_membuka_halaman_ubah_password(): void
    {
        $this->actingAs($this->user())
            ->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('Ubah Password');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('account.password.edit'))->assertRedirect(route('login'));
    }

    public function test_password_berhasil_diubah(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'passwordlama',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertRedirect(route('account.password.edit'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordbaru123', $user->fresh()->password));
    }

    public function test_password_saat_ini_salah_ditolak(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'salah',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('passwordlama', $user->fresh()->password));
    }

    public function test_konfirmasi_tidak_cocok_ditolak(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'passwordlama',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'beda123',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_baru_harus_berbeda_dari_yang_lama(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'passwordlama',
            'password' => 'passwordlama',
            'password_confirmation' => 'passwordlama',
        ])->assertSessionHasErrors('password');
    }

    public function test_semua_peran_bisa_mengubah_password(): void
    {
        foreach (['doctor', 'editor', 'admin'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->put(route('account.password.update'), [
                'current_password' => 'passwordlama',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])->assertRedirect(route('account.password.edit'));

            $this->assertTrue(Hash::check('passwordbaru123', $user->fresh()->password), "Gagal untuk role {$role}");
        }
    }
}
