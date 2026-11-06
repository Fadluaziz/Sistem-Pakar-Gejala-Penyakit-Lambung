<?php

namespace App\Features\DiseaseDetection\Services;

use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Rule;
use App\Features\DiseaseDetection\Models\Symptom;
use Illuminate\Support\Facades\DB;

/**
 * Pengelolaan basis aturan oleh editor: matriks bobot, data gejala, dan data
 * penyakit.
 *
 * Penghapusan DICEGAH bila data sudah dipakai riwayat konsultasi pasien — karena
 * foreign key di database bersifat cascade, penghapusan akan ikut menghapus
 * riwayat diagnosa / gejala yang dilaporkan pasien.
 */
class KnowledgeBaseService
{
    /**
     * Simpan matriks bobot aturan.
     *
     * Bentuk input: weights[disease_id][symptom_id] = 0..5. Nilai > 0 membuat
     * atau memperbarui aturan; nilai 0 / kosong menghapus aturan.
     *
     * @param  array<int|string, array<int|string, int|string|null>>  $weights
     */
    public function saveMatrix(array $weights): void
    {
        DB::transaction(function () use ($weights): void {
            foreach ($weights as $diseaseId => $symptomWeights) {
                if (! is_array($symptomWeights)) {
                    continue;
                }

                foreach ($symptomWeights as $symptomId => $weight) {
                    $weight = (int) ($weight ?? 0);

                    if ($weight > 0) {
                        Rule::updateOrCreate(
                            [
                                'disease_id' => (int) $diseaseId,
                                'symptom_id' => (int) $symptomId,
                            ],
                            ['weight' => $weight],
                        );

                        continue;
                    }

                    Rule::where('disease_id', (int) $diseaseId)
                        ->where('symptom_id', (int) $symptomId)
                        ->delete();
                }
            }
        });
    }

    /**
     * Matriks bobot untuk ditampilkan di editor: [disease_id][symptom_id] => weight.
     *
     * @return array<int, array<int, int>>
     */
    public function weightMatrix(): array
    {
        $matrix = [];

        foreach (Rule::query()->get(['disease_id', 'symptom_id', 'weight']) as $rule) {
            $matrix[(int) $rule->disease_id][(int) $rule->symptom_id] = (int) $rule->weight;
        }

        return $matrix;
    }

    /**
     * Id gejala yang sudah dipakai pada riwayat konsultasi (tidak boleh dihapus).
     *
     * @return array<int, int>
     */
    public function usedSymptomIds(): array
    {
        return Symptom::has('consultationSessions')->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    /**
     * Id penyakit yang sudah punya riwayat diagnosa (tidak boleh dihapus).
     *
     * @return array<int, int>
     */
    public function usedDiseaseIds(): array
    {
        return Disease::has('diagnoses')->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    /**
     * @param  array{code: string, name: string, category: string}  $data
     */
    public function createSymptom(array $data): Symptom
    {
        return Symptom::create($data);
    }

    /**
     * @param  array{code: string, name: string, category: string}  $data
     */
    public function updateSymptom(Symptom $symptom, array $data): void
    {
        $symptom->update($data);
    }

    public function canDeleteSymptom(Symptom $symptom): bool
    {
        return ! $symptom->consultationSessions()->exists();
    }

    /**
     * Hapus gejala; kembalikan false bila sudah dipakai riwayat konsultasi.
     */
    public function deleteSymptom(Symptom $symptom): bool
    {
        if (! $this->canDeleteSymptom($symptom)) {
            return false;
        }

        $symptom->delete();

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDisease(array $data): Disease
    {
        return Disease::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDisease(Disease $disease, array $data): void
    {
        $disease->update($data);
    }

    public function canDeleteDisease(Disease $disease): bool
    {
        return ! $disease->diagnoses()->exists();
    }

    /**
     * Hapus penyakit; kembalikan false bila sudah punya riwayat diagnosa.
     */
    public function deleteDisease(Disease $disease): bool
    {
        if (! $this->canDeleteDisease($disease)) {
            return false;
        }

        $disease->delete();

        return true;
    }
}
