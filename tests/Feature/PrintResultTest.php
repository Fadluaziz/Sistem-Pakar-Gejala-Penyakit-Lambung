<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintResultTest extends TestCase
{
    use RefreshDatabase;

    private function sessionFor(User $user): ConsultationSession
    {
        return ConsultationSession::create([
            'user_id' => $user->id,
            'status' => ConsultationSession::STATUS_INSUFFICIENT,
            'has_red_flag' => false,
        ]);
    }

    public function test_pasien_bisa_membuka_halaman_cetak_hasil(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'nik' => '3201234567890123',
            'gender' => 'Perempuan',
        ]);
        $session = $this->sessionFor($user);

        $response = $this->actingAs($user)->get(route('detection.result.print', $session));

        $response->assertOk();
        $response->assertSee('Laporan Hasil Diagnosa');
        $response->assertSee('Identitas Pasien');
        $response->assertSee('3201234567890123');
    }

    public function test_pasien_tidak_bisa_mencetak_hasil_pasien_lain(): void
    {
        $owner = User::factory()->create(['role' => 'patient']);
        $other = User::factory()->create(['role' => 'patient']);
        $session = $this->sessionFor($owner);

        $this->actingAs($other)
            ->get(route('detection.result.print', $session))
            ->assertForbidden();
    }

    public function test_tamu_tidak_bisa_membuka_halaman_cetak(): void
    {
        $owner = User::factory()->create(['role' => 'patient']);
        $session = $this->sessionFor($owner);

        $this->get(route('detection.result.print', $session))
            ->assertRedirect(route('login'));
    }
}
