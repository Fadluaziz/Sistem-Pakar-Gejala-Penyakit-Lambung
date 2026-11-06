<?php

namespace App\Features\DiseaseDetection\Services;

use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Rule;
use App\Features\DiseaseDetection\Models\Symptom;
use Illuminate\Support\Collection;

/**
 * Mesin inferensi forward chaining berbobot.
 *
 * Penelusuran berjalan dari fakta (gejala yang dipilih pasien) menuju konklusi
 * (penyakit). Setiap premis pada tabel `rules` yang gejalanya muncul menambahkan
 * bobotnya ke penyakit terkait; keyakinan = bobot terkumpul / total bobot penyakit.
 *
 * Service ini sengaja tidak menyentuh HTTP Request/Response maupun menulis ke
 * database, sehingga bisa diuji langsung.
 */
class InferenceService
{
    /**
     * Minimal gejala terpilih. Satu gejala terlalu ambigu untuk disimpulkan.
     */
    public const MIN_SYMPTOMS = 2;

    /**
     * Ambang keyakinan (%) agar sebuah penyakit boleh disebut sebagai kesimpulan.
     */
    public const MIN_CONFIDENCE = 40.0;

    public const STATUS_DIAGNOSED = 'diagnosed';
    public const STATUS_INCONCLUSIVE = 'inconclusive';
    public const STATUS_INSUFFICIENT = 'insufficient';

    /**
     * Jalankan inferensi untuk sekumpulan id gejala.
     *
     * @param  array<int, int|string>  $symptomIds
     * @return array{
     *     status: string,
     *     primary: array<string, mixed>|null,
     *     candidates: array<int, array<string, mixed>>,
     *     red_flags: array<int, string>,
     *     has_red_flag: bool,
     *     selected_count: int
     * }
     */
    public function execute(array $symptomIds): array
    {
        $selected = collect($symptomIds)
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $candidates = $this->scoreCandidates($selected);
        $redFlags = $this->detectRedFlags($selected);
        $top = $candidates->first();

        $status = match (true) {
            $selected->count() < self::MIN_SYMPTOMS => self::STATUS_INSUFFICIENT,
            $top === null || $top['confidence'] < self::MIN_CONFIDENCE => self::STATUS_INCONCLUSIVE,
            default => self::STATUS_DIAGNOSED,
        };

        return [
            'status' => $status,
            'primary' => $status === self::STATUS_DIAGNOSED ? $top : null,
            'candidates' => $candidates->all(),
            'red_flags' => $redFlags,
            'has_red_flag' => $redFlags !== [],
            'selected_count' => $selected->count(),
        ];
    }

    /**
     * Hitung keyakinan tiap penyakit, terurut menurun.
     *
     * @param  Collection<int, int>  $selected
     * @return Collection<int, array<string, mixed>>
     */
    private function scoreCandidates(Collection $selected): Collection
    {
        return Disease::with('rules.symptom')
            ->get()
            ->map(function (Disease $disease) use ($selected): array {
                // Premis yang gejalanya dipilih pasien — inilah jejak penalaran yang
                // nanti ditampilkan sebagai penjelasan "mengapa" pada halaman hasil.
                $matched = $disease->rules
                    ->filter(fn (Rule $rule): bool => $selected->contains($rule->symptom_id))
                    ->map(fn (Rule $rule): array => [
                        'symptom_id' => $rule->symptom_id,
                        'code' => $rule->symptom->code,
                        'name' => $rule->symptom->name,
                        'category' => $rule->symptom->category,
                        'weight' => (int) $rule->weight,
                    ])
                    ->sortByDesc('weight')
                    ->values()
                    ->all();

                $totalWeight = (int) $disease->rules->sum('weight');
                $matchedWeight = (int) array_sum(array_column($matched, 'weight'));

                return [
                    'disease_id' => $disease->id,
                    'code' => $disease->code,
                    'name' => $disease->name,
                    'summary' => $disease->summary,
                    'description' => $disease->description,
                    'treatment' => $disease->treatment,
                    'prevention' => $disease->prevention,
                    'matched' => $matched,
                    'matched_weight' => $matchedWeight,
                    'total_weight' => $totalWeight,
                    'confidence' => $totalWeight > 0
                        ? round($matchedWeight / $totalWeight * 100, 2)
                        : 0.0,
                ];
            })
            // Penyakit tanpa satu pun gejala yang cocok tidak ditampilkan.
            ->filter(fn (array $candidate): bool => $candidate['confidence'] > 0)
            ->sort(fn (array $a, array $b): int => [$b['confidence'], $b['matched_weight']]
                <=> [$a['confidence'], $a['matched_weight']])
            ->values();
    }

    /**
     * Nama gejala tanda bahaya yang muncul pada pilihan pasien.
     *
     * Tanda bahaya bukan kode hardcoded, melainkan gejala berkategori
     * "Tanda Bahaya" — sehingga gejala yang ditambah editor ikut terdeteksi.
     *
     * @param  Collection<int, int>  $selected
     * @return array<int, string>
     */
    private function detectRedFlags(Collection $selected): array
    {
        return Symptom::where('category', Symptom::CATEGORY_BAHAYA)
            ->whereIn('id', $selected->all())
            ->pluck('name')
            ->all();
    }
}
