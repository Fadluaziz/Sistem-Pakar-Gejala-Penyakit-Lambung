<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Symptom;
use App\Features\DiseaseDetection\Services\ConsultationService;
use App\Models\User;
use Database\Seeders\KnowledgeBaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KnowledgeBaseSeeder::class);
    }

    private function patient(): User
    {
        return User::factory()->create(['role' => 'patient']);
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<int, int>
     */
    private function idsFor(array $codes): array
    {
        return Symptom::whereIn('code', $codes)->pluck('id')->all();
    }

    public function test_setiap_kode_gejala_pada_definisi_langkah_ada_di_database(): void
    {
        $codes = collect(app(ConsultationService::class)->steps())
            ->pluck('codes')
            ->flatten();

        $missing = $codes->diff(Symptom::pluck('code'))->values()->all();

        $this->assertSame(
            [],
            $missing,
            'Kode gejala berikut dirujuk ConsultationService tetapi tidak ada di tabel symptoms: '
                .implode(', ', $missing),
        );
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('detection.consultation'))
            ->assertRedirect(route('login'));
    }

    public function test_editor_tidak_boleh_mengakses_konsultasi(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('detection.consultation'))
            ->assertForbidden();
    }

    public function test_pasien_menyelesaikan_konsultasi_dan_melihat_hasilnya(): void
    {
        $user = $this->patient();

        $this->actingAs($user)
            ->get(route('detection.consultation'))
            ->assertRedirect(route('detection.consultation.show', 'utama'));

        // Bagian 1 — nyeri ulu hati; lanjut ke pola nyeri.
        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'utama'), [
                'symptoms' => $this->idsFor(['G01']),
            ])
            ->assertRedirect(route('detection.consultation.show', 'nyeri'));

        // Bagian 2 — pola khas tukak lambung; keempat bagian selalu berurutan.
        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'nyeri'), [
                'symptoms' => $this->idsFor(['G09', 'G11']),
            ])
            ->assertRedirect(route('detection.consultation.show', 'refluks'));

        // Bagian 3 — tanpa gejala refluks.
        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'refluks'), ['symptoms' => []])
            ->assertRedirect(route('detection.consultation.show', 'bahaya'));

        // Bagian 4 — tanpa tanda bahaya, konsultasi selesai.
        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'bahaya'), ['symptoms' => []])
            ->assertRedirect(route('detection.result', ConsultationSession::firstOrFail()));

        $session = ConsultationSession::firstOrFail();
        $this->assertSame(ConsultationSession::STATUS_DIAGNOSED, $session->status);
        $this->assertSame(3, $session->symptoms()->count());

        $this->actingAs($user)
            ->get(route('detection.result', $session))
            ->assertOk()
            ->assertSee('Tukak Lambung');

        $this->actingAs($user)
            ->get(route('detection.history'))
            ->assertOk()
            ->assertSee('Tukak Lambung');
    }

    public function test_keempat_bagian_selalu_tersedia_tanpa_percabangan(): void
    {
        $user = $this->patient();

        // Belum ada jawaban apa pun, tetapi semua bagian tetap bisa dibuka.
        foreach (['utama', 'nyeri', 'refluks', 'bahaya'] as $slug) {
            $this->actingAs($user)
                ->get(route('detection.consultation.show', $slug))
                ->assertOk();
        }
    }

    public function test_konsultasi_menampilkan_pertanyaan_ya_tidak_per_gejala(): void
    {
        $user = $this->patient();

        $this->actingAs($user)
            ->get(route('detection.consultation.show', 'utama'))
            ->assertOk()
            ->assertSee('Bagian 1 dari 4')
            // Pertanyaan dalam bentuk kalimat, bukan sekadar nama gejala.
            ->assertSee('Apakah Anda merasakan nyeri atau perih di ulu hati?')
            ->assertSee('Ya')
            ->assertSee('Tidak')
            // Field yang dikirim tetap symptoms[] (dipakai validasi & service).
            ->assertSee('name="symptoms[]"', false);
    }

    public function test_seluruh_25_pertanyaan_terbagi_ke_empat_bagian(): void
    {
        $service = app(ConsultationService::class);

        $totalQuestions = collect($service->steps())
            ->sum(fn (array $step): int => $service->questionsForStep($step['slug'])->count());

        $this->assertSame(25, $totalQuestions, 'Konsultasi harus menyediakan 25 pertanyaan.');

        foreach ($service->steps() as $step) {
            $this->assertNotEmpty(
                $service->questionsForStep($step['slug']),
                "Bagian {$step['slug']} tidak punya pertanyaan.",
            );
        }
    }

    public function test_jawaban_langkah_diperbarui_tidak_digandakan_saat_kembali(): void
    {
        $user = $this->patient();

        $this->actingAs($user)->post(route('detection.consultation.save', 'utama'), [
            'symptoms' => $this->idsFor(['G01', 'G02']),
        ]);

        // Kembali ke langkah yang sama dan ubah pilihan.
        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'utama'), [
                'symptoms' => $this->idsFor(['G01']),
            ])
            ->assertRedirect(route('detection.consultation.show', 'nyeri'));

        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'nyeri'), ['symptoms' => []]);

        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'refluks'), ['symptoms' => []]);

        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'bahaya'), ['symptoms' => []]);

        $session = ConsultationSession::firstOrFail();

        // G01 tetap ada, G02 ikut terbuang karena tidak lagi dicentang.
        $this->assertSame(1, $session->symptoms()->count());
    }

    public function test_gejala_tidak_dikenal_ditolak_validasi(): void
    {
        $user = $this->patient();

        $this->actingAs($user)
            ->post(route('detection.consultation.save', 'utama'), ['symptoms' => [999999]])
            ->assertSessionHasErrors('symptoms.0');
    }

    public function test_pasien_tidak_bisa_membuka_hasil_pasien_lain(): void
    {
        $owner = $this->patient();
        $other = $this->patient();

        $session = ConsultationSession::create([
            'user_id' => $owner->id,
            'status' => ConsultationSession::STATUS_INCONCLUSIVE,
            'has_red_flag' => false,
        ]);

        $this->actingAs($other)
            ->get(route('detection.result', $session))
            ->assertForbidden();
    }

    public function test_pasien_bisa_membuka_halaman_edukasi_penyakit(): void
    {
        $user = $this->patient();

        $this->actingAs($user)
            ->get(route('detection.diseases'))
            ->assertOk()
            ->assertSee('Mengenal Penyakit Lambung')
            ->assertSee('Gastritis')
            ->assertSee('Dispepsia (Maag)')
            ->assertSee('Tukak Lambung (Ulkus Peptikum)')
            ->assertSee('GERD (Refluks Gastroesofageal)');
    }

    public function test_tamu_tidak_bisa_membuka_halaman_edukasi(): void
    {
        $this->get(route('detection.diseases'))
            ->assertRedirect(route('login'));
    }

    public function test_halaman_hasil_menjelaskan_jejak_penalaran_dan_disclaimer(): void
    {
        $user = $this->patient();

        $this->actingAs($user)->post(route('detection.consultation.save', 'utama'), [
            'symptoms' => $this->idsFor(['G01']),
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'nyeri'), [
            'symptoms' => $this->idsFor(['G09', 'G11']),
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'refluks'), [
            'symptoms' => [],
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'bahaya'), [
            'symptoms' => [],
        ]);

        $session = ConsultationSession::firstOrFail();
        $top = $session->diagnoses()->where('rank', 1)->firstOrFail();

        // Jejak penalaran tersimpan: tiga premis dengan bobotnya masing-masing.
        $this->assertNotNull($top->breakdown);
        $this->assertEqualsCanonicalizing(
            ['G01', 'G09', 'G11'],
            array_column($top->breakdown, 'code'),
        );
        $this->assertSame(14, $top->matchedWeight());
        $this->assertSame(26, $top->total_weight);

        $this->actingAs($user)
            ->get(route('detection.result', $session))
            ->assertOk()
            // Penjelasan perhitungan dan tiap premis yang menyumbang bobot.
            ->assertSee('Bagaimana Kesimpulan Ini Diperoleh')
            ->assertSee('bobot 5')
            ->assertSee('14')
            ->assertSee('26')
            // Pengobatan dan pencegahan.
            ->assertSee('Cara Pengobatan')
            ->assertSee('Cara Pencegahan')
            // Disclaimer penutup yang diminta.
            ->assertSee('Disclaimer: diagnosa pada website ini bersifat sementara')
            ->assertSee('silahkan kunjungi dokter terdekat untuk mendapatkan pengobatan lebih lanjut');
    }

    public function test_halaman_hasil_belum_disimpulkan_tidak_menampilkan_pengobatan(): void
    {
        $user = $this->patient();

        $this->actingAs($user)->post(route('detection.consultation.save', 'utama'), [
            'symptoms' => $this->idsFor(['G01']),
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'nyeri'), [
            'symptoms' => [],
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'refluks'), [
            'symptoms' => [],
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'bahaya'), [
            'symptoms' => [],
        ]);

        $session = ConsultationSession::firstOrFail();

        $this->actingAs($user)
            ->get(route('detection.result', $session))
            ->assertOk()
            ->assertSee('Belum dapat disimpulkan')
            // Pengobatan tidak boleh tampil tanpa kesimpulan yang pasti.
            ->assertDontSee('Cara Pengobatan')
            ->assertDontSee('Cara Pencegahan')
            // Disclaimer tetap muncul walau belum ada kesimpulan.
            ->assertSee('Disclaimer: diagnosa pada website ini bersifat sementara');
    }

    public function test_tanda_bahaya_ditampilkan_pada_halaman_hasil(): void
    {
        $user = $this->patient();

        $this->actingAs($user)->post(route('detection.consultation.save', 'utama'), [
            'symptoms' => $this->idsFor(['G01']),
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'nyeri'), [
            'symptoms' => $this->idsFor(['G09', 'G11']),
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'refluks'), [
            'symptoms' => [],
        ]);

        $this->actingAs($user)->post(route('detection.consultation.save', 'bahaya'), [
            'symptoms' => $this->idsFor(['G22']),
        ]);

        $session = ConsultationSession::firstOrFail();
        $this->assertTrue($session->has_red_flag);

        $this->actingAs($user)
            ->get(route('detection.result', $session))
            ->assertOk()
            ->assertSee('tanda bahaya terdeteksi');
    }
}
