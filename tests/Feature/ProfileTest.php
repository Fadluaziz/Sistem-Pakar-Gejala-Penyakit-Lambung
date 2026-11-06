<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function patient(): User
    {
        return User::factory()->create(['role' => 'patient']);
    }

    public function test_pasien_bisa_membuka_halaman_profil(): void
    {
        $response = $this->actingAs($this->patient())->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Profil Saya');
    }

    public function test_pasien_bisa_menyimpan_data_diri(): void
    {
        $user = $this->patient();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Budi Santoso',
            'email' => $user->email,
            'nik' => '3201234567890123',
            'birth_date' => '1990-05-10',
            'gender' => 'Laki-laki',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 1',
            'occupation' => 'Pegawai',
            'blood_type' => 'O',
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('3201234567890123', $user->nik);
        $this->assertSame('Laki-laki', $user->gender);
        $this->assertSame('O', $user->blood_type);
    }

    public function test_nik_harus_16_digit(): void
    {
        $user = $this->patient();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'nik' => '123',
        ])->assertSessionHasErrors('nik');
    }

    public function test_non_pasien_ditolak_membuka_profil(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);

        $this->actingAs($doctor)->get(route('profile.edit'))->assertForbidden();
    }

    public function test_hasil_diagnosa_menampilkan_data_diri_pasien(): void
    {
        $user = $this->patient();
        $user->update(['nik' => '3201234567890123', 'gender' => 'Perempuan']);

        $session = ConsultationSession::create([
            'user_id' => $user->id,
            'status' => ConsultationSession::STATUS_INSUFFICIENT,
            'has_red_flag' => false,
        ]);

        $response = $this->actingAs($user)->get(route('detection.result', $session));

        $response->assertOk();
        $response->assertSee('Data Pasien');
        $response->assertSee('3201234567890123');
        $response->assertSee('Cetak / Simpan PDF');
    }

    public function test_halaman_profil_menampilkan_ringkasan_data_diri(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'nik' => '3201234567890123',
            'gender' => 'Perempuan',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Profil Lengkap');
        $response->assertSee('3201234567890123');
        $response->assertSee('Perempuan');
        $response->assertSee('data terisi');
    }
}
