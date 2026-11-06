<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Models\User;
use Database\Seeders\KnowledgeBaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KnowledgeBaseSeeder::class);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/patient/dashboard')->assertRedirect(route('login'));
    }

    public function test_dashboard_pasien_menampilkan_sambutan_ringkasan_dan_tips(): void
    {
        $patient = User::factory()->create(['role' => 'patient', 'name' => 'Andi']);

        ConsultationSession::create([
            'user_id' => $patient->id,
            'status' => ConsultationSession::STATUS_DIAGNOSED,
            'has_red_flag' => false,
        ]);

        $this->actingAs($patient)
            ->get('/patient/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang, Andi!')
            ->assertSee('Total Konsultasi')
            ->assertSee('Konsultasi Terakhir')
            ->assertSee('Hasil Terakhir')
            ->assertSee('Tips Menjaga Kesehatan Lambung')
            // Bagian sambutan tidak lagi memuat tombol aksi.
            ->assertDontSee('Mulai Konsultasi');
    }

    public function test_dashboard_pasien_tanpa_konsultasi_tetap_tampil(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)
            ->get('/patient/dashboard')
            ->assertOk()
            ->assertSee('Total Konsultasi')
            ->assertSee('belum ada konsultasi');
    }

    public function test_dashboard_dokter_menampilkan_ringkasan_peninjauan(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);

        $this->actingAs($doctor)
            ->get('/doctor/dashboard')
            ->assertOk()
            ->assertSee('Perlu Ditinjau')
            ->assertSee('Total Diagnosa')
            // Tombol aksi pada bagian sambutan sudah dihapus.
            ->assertDontSee('Tinjau Diagnosa →');
    }

    public function test_dashboard_editor_menampilkan_ringkasan_basis_aturan(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get('/editor/dashboard')
            ->assertOk()
            ->assertSee('Penyakit')
            ->assertSee('Gejala')
            ->assertSee('Aturan');
    }

    public function test_dashboard_admin_menampilkan_ringkasan_pengguna(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Pengguna')
            ->assertSee('Konsultasi');
    }
}
