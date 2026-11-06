<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\Symptom;
use App\Features\DiseaseDetection\Services\InferenceService;
use Database\Seeders\KnowledgeBaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InferenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InferenceService $inference;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KnowledgeBaseSeeder::class);
        $this->inference = app(InferenceService::class);
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<int, int>
     */
    private function idsFor(array $codes): array
    {
        return Symptom::whereIn('code', $codes)->pluck('id')->all();
    }

    public function test_pola_nyeri_khas_menghasilkan_tukak_lambung(): void
    {
        $result = $this->inference->execute($this->idsFor(['G01', 'G09', 'G11']));

        $this->assertSame(InferenceService::STATUS_DIAGNOSED, $result['status']);
        $this->assertSame('Tukak Lambung (Ulkus Peptikum)', $result['primary']['name']);
        // G01(4) + G09(5) + G11(5) = 14 dari total bobot 26.
        $this->assertEqualsWithDelta(53.85, $result['primary']['confidence'], 0.01);

        // Gastritis harus muncul sebagai kandidat kedua.
        $this->assertSame('Gastritis', $result['candidates'][1]['name']);
    }

    public function test_kembung_dan_cepat_kenyang_menghasilkan_dispepsia(): void
    {
        $result = $this->inference->execute($this->idsFor(['G02', 'G03', 'G04', 'G08']));

        $this->assertSame(InferenceService::STATUS_DIAGNOSED, $result['status']);
        $this->assertSame('Dispepsia (Maag)', $result['primary']['name']);
        // G02(5) + G03(5) + G04(4) + G08(4) = 18 dari 21.
        $this->assertEqualsWithDelta(85.71, $result['primary']['confidence'], 0.01);
    }

    public function test_heartburn_dan_regurgitasi_menghasilkan_gerd(): void
    {
        $result = $this->inference->execute($this->idsFor(['G16', 'G17', 'G19']));

        $this->assertSame(InferenceService::STATUS_DIAGNOSED, $result['status']);
        $this->assertSame('GERD (Refluks Gastroesofageal)', $result['primary']['name']);
        // G16(5) + G17(5) + G19(4) = 14 dari 27.
        $this->assertEqualsWithDelta(51.85, $result['primary']['confidence'], 0.01);
    }

    public function test_nyeri_dengan_mual_dan_muntah_menghasilkan_gastritis(): void
    {
        $result = $this->inference->execute($this->idsFor(['G01', 'G05', 'G06', 'G07']));

        $this->assertSame(InferenceService::STATUS_DIAGNOSED, $result['status']);
        $this->assertSame('Gastritis', $result['primary']['name']);
        // G01(5) + G05(4) + G06(3) + G07(3) = 15 dari 23.
        $this->assertEqualsWithDelta(65.22, $result['primary']['confidence'], 0.01);
    }

    public function test_satu_gejala_saja_belum_cukup_untuk_disimpulkan(): void
    {
        $result = $this->inference->execute($this->idsFor(['G01']));

        $this->assertSame(InferenceService::STATUS_INSUFFICIENT, $result['status']);
        $this->assertNull($result['primary']);
        // Kandidat tetap dilaporkan walau statusnya belum cukup.
        $this->assertNotEmpty($result['candidates']);
    }

    public function test_kombinasi_ambigu_di_bawah_ambang_dianggap_belum_dapat_disimpulkan(): void
    {
        // G01(5) + G13(2) = 7 dari 23 -> 30.4%, di bawah ambang 40%.
        $result = $this->inference->execute($this->idsFor(['G01', 'G13']));

        $this->assertSame(InferenceService::STATUS_INCONCLUSIVE, $result['status']);
        $this->assertNull($result['primary']);
    }

    public function test_tanda_bahaya_memicu_peringatan(): void
    {
        $result = $this->inference->execute($this->idsFor(['G01', 'G09', 'G11', 'G22']));

        $this->assertTrue($result['has_red_flag']);
        $this->assertContains('BAB berwarna hitam seperti aspal', $result['red_flags']);
    }

    public function test_tanpa_tanda_bahaya_tidak_ada_peringatan(): void
    {
        $result = $this->inference->execute($this->idsFor(['G01', 'G09', 'G11']));

        $this->assertFalse($result['has_red_flag']);
        $this->assertSame([], $result['red_flags']);
    }

    public function test_penyakit_tanpa_gejala_cocok_tidak_muncul_sebagai_kandidat(): void
    {
        $result = $this->inference->execute($this->idsFor(['G16', 'G17']));

        $codes = array_column($result['candidates'], 'code');

        $this->assertContains('P04', $codes);
        $this->assertNotContains('P02', $codes, 'Dispepsia tidak boleh muncul tanpa gejala yang cocok.');
    }
}
