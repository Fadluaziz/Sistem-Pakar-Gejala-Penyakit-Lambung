<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Diagnosis;
use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Symptom;
use App\Models\User;
use Database\Seeders\KnowledgeBaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KnowledgeBaseSeeder::class);
    }

    private function doctor(): User
    {
        return User::factory()->create(['role' => 'doctor']);
    }

    private function patient(): User
    {
        return User::factory()->create(['role' => 'patient']);
    }

    private function makeSession(User $patient): ConsultationSession
    {
        $session = ConsultationSession::create([
            'user_id' => $patient->id,
            'status' => ConsultationSession::STATUS_DIAGNOSED,
            'has_red_flag' => false,
        ]);

        $disease = Disease::where('code', 'P03')->firstOrFail();
        $gastro = Disease::where('code', 'P01')->firstOrFail();

        Diagnosis::create([
            'consultation_session_id' => $session->id,
            'disease_id' => $disease->id,
            'confidence' => 53.85,
            'breakdown' => [
                ['code' => 'G01', 'name' => 'Nyeri atau perih di ulu hati', 'weight' => 4],
                ['code' => 'G09', 'name' => 'Nyeri ulu hati saat perut kosong', 'weight' => 5],
                ['code' => 'G11', 'name' => 'Nyeri ulu hati reda setelah makan', 'weight' => 5],
            ],
            'total_weight' => 26,
            'rank' => 1,
        ]);

        Diagnosis::create([
            'consultation_session_id' => $session->id,
            'disease_id' => $gastro->id,
            'confidence' => 21.74,
            'breakdown' => [],
            'total_weight' => 23,
            'rank' => 2,
        ]);

        $symptomIds = Symptom::whereIn('code', ['G01', 'G09', 'G11'])->pluck('id')->all();
        $session->symptoms()->sync($symptomIds);

        return $session;
    }

    public function test_pasien_tidak_bisa_membuka_peninjauan_dokter(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->get(route('doctor.review.index'))
            ->assertForbidden();
    }

    public function test_editor_tidak_bisa_membuka_peninjauan_dokter(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('doctor.review.index'))
            ->assertForbidden();
    }

    public function test_dokter_meninjau_daftar_dan_hasil(): void
    {
        $doctor = $this->doctor();
        $patient = $this->patient();
        $session = $this->makeSession($patient);

        $this->actingAs($doctor)
            ->get(route('doctor.review.index'))
            ->assertOk()
            ->assertSee('Riwayat Diagnosa Pasien');

        $this->actingAs($doctor)
            ->get(route('doctor.review.show', $session->id))
            ->assertOk()
            ->assertSee('Tukak Lambung')
            ->assertSee('Bagaimana Kesimpulan Ini Diperoleh')
            ->assertSee('bobot 5');
    }

    public function test_dokter_memvalidasi_hasil_dengan_catatan(): void
    {
        $doctor = $this->doctor();
        $patient = $this->patient();
        $session = $this->makeSession($patient);

        $this->actingAs($doctor)
            ->post(route('doctor.review.validate', $session->id), [
                'validation' => ConsultationSession::VALIDATION_CORRECT,
                'doctor_note' => 'Kesimpulan sesuai, perlu endoskopi.',
            ])
            ->assertRedirect(route('doctor.review.show', $session->id));

        $session->refresh();

        $this->assertTrue($session->isValidated());
        $this->assertSame(ConsultationSession::VALIDATION_CORRECT, $session->validation);
        $this->assertSame('Kesimpulan sesuai, perlu endoskopi.', $session->doctor_note);
        $this->assertSame($doctor->id, $session->doctor_id);
        $this->assertNotNull($session->validated_at);
    }

    public function test_dokter_memvalidasi_sebagai_salah(): void
    {
        $doctor = $this->doctor();
        $session = $this->makeSession($this->patient());

        $this->actingAs($doctor)
            ->post(route('doctor.review.validate', $session->id), [
                'validation' => ConsultationSession::VALIDATION_INCORRECT,
                'doctor_note' => null,
            ])
            ->assertRedirect(route('doctor.review.show', $session->id));

        $this->assertSame(ConsultationSession::VALIDATION_INCORRECT, $session->fresh()->validation);
    }

    public function test_validasi_dengan_nilai_ilegal_ditolak(): void
    {
        $doctor = $this->doctor();
        $session = $this->makeSession($this->patient());

        $this->actingAs($doctor)
            ->post(route('doctor.review.validate', $session->id), [
                'validation' => 'tidak-pasti',
            ])
            ->assertSessionHasErrors('validation');

        $this->assertFalse($session->fresh()->isValidated());
    }

    public function test_kartu_status_dokter_tampil_di_daftar(): void
    {
        $doctor = $this->doctor();
        $session = $this->makeSession($this->patient());

        $this->actingAs($doctor)
            ->post(route('doctor.review.validate', $session->id), [
                'validation' => ConsultationSession::VALIDATION_CORRECT,
            ]);

        $this->actingAs($doctor)
            ->get(route('doctor.review.filter', 'validated'))
            ->assertOk()
            ->assertSee('Riwayat Diagnosa Pasien');

        $this->actingAs($doctor)
            ->get(route('doctor.review.filter', 'unvalidated'))
            ->assertOk();
    }

    public function test_dokter_melihat_form_tambah_diagnosa_manual(): void
    {
        $doctor = $this->doctor();

        $this->actingAs($doctor)
            ->get(route('doctor.review.create'))
            ->assertOk()
            ->assertSee('Tambah Diagnosa Manual')
            ->assertSee('Pasien');
    }

    public function test_pasien_tidak_bisa_membuka_form_tambah_diagnosa_manual(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->get(route('doctor.review.create'))
            ->assertForbidden();
    }

    public function test_dokter_menambah_diagnosa_manual(): void
    {
        $doctor = $this->doctor();
        $patient = $this->patient();

        $disease = Disease::where('code', 'P03')->firstOrFail();
        $symptomIds = Symptom::whereIn('code', ['G01', 'G09', 'G11'])->pluck('id')->all();

        $this->actingAs($doctor)
            ->post(route('doctor.review.store'), [
                'patient_id' => $patient->id,
                'disease_id' => $disease->id,
                'symptoms' => $symptomIds,
                'doctor_note' => 'Diperiksa langsung, dengan endoskopi.',
            ])
            ->assertRedirect();

        $session = ConsultationSession::firstOrFail();
        $this->assertSame(ConsultationSession::SOURCE_MANUAL, $session->source);
        $this->assertSame($patient->id, $session->user_id);
        $this->assertSame($doctor->id, $session->doctor_id);
        $this->assertSame(ConsultationSession::STATUS_DIAGNOSED, $session->status);
        $this->assertTrue($session->isValidated());
        $this->assertSame('Diperiksa langsung, dengan endoskopi.', $session->doctor_note);
        $this->assertSame($disease->id, $session->diagnoses()->first()->disease_id);
        $this->assertSame(100.0, $session->diagnoses()->first()->confidence);
        $this->assertCount(3, $session->symptoms);
    }

    public function test_menambah_diagnosa_manual_perlu_pasien_dan_penyakit_valid(): void
    {
        $doctor = $this->doctor();

        $this->actingAs($doctor)
            ->post(route('doctor.review.store'), [
                'disease_id' => 999999,
            ])
            ->assertSessionHasErrors(['patient_id', 'disease_id']);
    }

    public function test_dokter_bisa_membatalkan_penilaian(): void
    {
        $doctor = $this->doctor();
        $session = $this->makeSession($this->patient());

        $this->actingAs($doctor)
            ->post(route('doctor.review.validate', $session->id), [
                'validation' => ConsultationSession::VALIDATION_CORRECT,
            ]);

        $session->refresh();

        $this->actingAs($doctor)
            ->delete(route('doctor.review.reset', $session->id))
            ->assertRedirect(route('doctor.review.show', $session->id));

        $session->refresh();

        $this->assertFalse($session->isValidated());
        $this->assertNull($session->validation);
        $this->assertNull($session->doctor_note);
    }
}