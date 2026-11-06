<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Diagnosis;
use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Rule;
use App\Features\DiseaseDetection\Models\Symptom;
use App\Features\DiseaseDetection\Services\ConsultationService;
use App\Models\User;
use Database\Seeders\KnowledgeBaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KnowledgeBaseSeeder::class);
    }

    private function editor(): User
    {
        return User::factory()->create(['role' => 'editor']);
    }

    public function test_editor_dan_admin_bisa_membuka_ketiga_halaman(): void
    {
        foreach ([$this->editor(), User::factory()->create(['role' => 'admin'])] as $user) {
            foreach (['editor.rules.index', 'editor.symptoms.index', 'editor.diseases.index'] as $route) {
                $this->actingAs($user)->get(route($route))->assertOk();
            }
        }
    }

    public function test_pasien_dan_dokter_ditolak(): void
    {
        foreach (['patient', 'doctor'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('editor.rules.index'))->assertForbidden();
            $this->actingAs($user)->get(route('editor.symptoms.index'))->assertForbidden();
            $this->actingAs($user)->get(route('editor.diseases.index'))->assertForbidden();
        }
    }

    public function test_menyimpan_matriks_membuat_mengubah_dan_menghapus_aturan(): void
    {
        $editor = $this->editor();
        $disease = Disease::where('code', 'P01')->firstOrFail();
        $symptomBaru = Symptom::where('code', 'G24')->firstOrFail();   // awalnya tanpa aturan
        $symptomAda = Symptom::where('code', 'G01')->firstOrFail();    // awalnya bobot 5

        $this->assertNull(Rule::where('disease_id', $disease->id)->where('symptom_id', $symptomBaru->id)->first());

        $this->actingAs($editor)
            ->put(route('editor.rules.update'), [
                'weights' => [
                    $disease->id => [
                        $symptomBaru->id => 3,   // buat baru
                        $symptomAda->id => 0,    // hapus yang ada
                    ],
                ],
            ])
            ->assertRedirect(route('editor.rules.index'));

        // Aturan baru tercipta dengan bobot 3.
        $this->assertSame(3, (int) Rule::where('disease_id', $disease->id)
            ->where('symptom_id', $symptomBaru->id)->firstOrFail()->weight);

        // Aturan lama (G01 untuk Gastritis) terhapus.
        $this->assertNull(Rule::where('disease_id', $disease->id)
            ->where('symptom_id', $symptomAda->id)->first());
    }

    public function test_bobot_di_luar_rentang_ditolak(): void
    {
        $editor = $this->editor();
        $disease = Disease::firstOrFail();
        $symptom = Symptom::firstOrFail();

        $this->actingAs($editor)
            ->put(route('editor.rules.update'), [
                'weights' => [$disease->id => [$symptom->id => 9]],
            ])
            ->assertSessionHasErrors("weights.{$disease->id}.{$symptom->id}");
    }

    public function test_editor_bisa_menambah_mengubah_dan_menghapus_gejala(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('editor.symptoms.store'), [
                'code' => 'G99', 'name' => 'Gejala Uji', 'category' => Symptom::CATEGORY_UMUM,
            ])
            ->assertRedirect(route('editor.symptoms.index'));

        $symptom = Symptom::where('code', 'G99')->firstOrFail();

        $this->actingAs($editor)
            ->put(route('editor.symptoms.update', $symptom), [
                'code' => 'G99', 'name' => 'Gejala Uji Diubah', 'category' => Symptom::CATEGORY_REFLUKS,
            ]);

        $this->assertSame('Gejala Uji Diubah', $symptom->fresh()->name);
        $this->assertSame(Symptom::CATEGORY_REFLUKS, $symptom->fresh()->category);

        $this->actingAs($editor)
            ->delete(route('editor.symptoms.destroy', $symptom))
            ->assertRedirect(route('editor.symptoms.index'));

        $this->assertNull(Symptom::find($symptom->id));
    }

    public function test_gejala_yang_sudah_dipakai_konsultasi_tidak_bisa_dihapus(): void
    {
        $editor = $this->editor();
        $patient = User::factory()->create(['role' => 'patient']);

        $symptom = Symptom::where('code', 'G01')->firstOrFail();

        $session = ConsultationSession::create([
            'user_id' => $patient->id,
            'status' => ConsultationSession::STATUS_INCONCLUSIVE,
            'has_red_flag' => false,
        ]);
        $session->symptoms()->sync([$symptom->id]);

        $this->actingAs($editor)
            ->delete(route('editor.symptoms.destroy', $symptom))
            ->assertRedirect(route('editor.symptoms.index'))
            ->assertSessionHas('error');

        $this->assertNotNull(Symptom::find($symptom->id));
    }

    public function test_editor_bisa_menambah_dan_menghapus_penyakit(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('editor.diseases.store'), [
                'code' => 'P99', 'name' => 'Penyakit Uji', 'summary' => 'Ringkasan uji',
            ])
            ->assertRedirect(route('editor.diseases.index'));

        $disease = Disease::where('code', 'P99')->firstOrFail();

        $this->actingAs($editor)
            ->delete(route('editor.diseases.destroy', $disease));

        $this->assertNull(Disease::find($disease->id));
    }

    public function test_penyakit_yang_punya_diagnosa_tidak_bisa_dihapus(): void
    {
        $editor = $this->editor();
        $patient = User::factory()->create(['role' => 'patient']);

        $disease = Disease::where('code', 'P03')->firstOrFail();

        $session = ConsultationSession::create([
            'user_id' => $patient->id,
            'status' => ConsultationSession::STATUS_DIAGNOSED,
            'has_red_flag' => false,
        ]);
        Diagnosis::create([
            'consultation_session_id' => $session->id,
            'disease_id' => $disease->id,
            'confidence' => 55.0,
            'breakdown' => [],
            'total_weight' => 26,
            'rank' => 1,
        ]);

        $this->actingAs($editor)
            ->delete(route('editor.diseases.destroy', $disease))
            ->assertRedirect(route('editor.diseases.index'))
            ->assertSessionHas('error');

        $this->assertNotNull(Disease::find($disease->id));
    }

    public function test_gejala_baru_muncul_di_konsultasi_sesuai_kategorinya(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('editor.symptoms.store'), [
            'code' => 'G98', 'name' => 'Keluhan Baru Uji', 'category' => Symptom::CATEGORY_UMUM,
        ])->assertRedirect(route('editor.symptoms.index'));

        Symptom::where('code', 'G98')->firstOrFail();

        $service = app(ConsultationService::class);

        $this->assertTrue(
            collect($service->questionsForStep('utama'))->contains('code', 'G98'),
            'Gejala baru berkategori Umum harus muncul pada bagian "utama".',
        );
    }
}
