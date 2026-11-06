<?php

namespace App\Features\DiseaseDetection\Services;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Diagnosis;
use App\Features\DiseaseDetection\Models\Disease;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Peninjauan dan validasi hasil diagnosa oleh dokter.
 *
 * Catatan medis dokter ditambahkan ke sesi konsultasi sebagai umpan balik atas
 * kesimpulan mesin inferensi — sehingga akurasi sistem terhadap penilaian pakar
 * bisa diukur.
 */
class DoctorValidationService
{
    /**
     * Daftar hasil diagnosa untuk ditinjau dokter.
     *
     * @return Collection<int, ConsultationSession>
     */
    public function listForReview(?string $filter = null): Collection
    {
        $query = ConsultationSession::query()
            ->with(['diagnoses' => fn ($query) => $query->orderBy('rank'), 'user', 'symptoms'])
            ->withCount('symptoms')
            ->has('diagnoses');

        if ($filter === 'validated') {
            $query->whereNotNull('validated_at');
        } elseif ($filter === 'unvalidated') {
            $query->whereNull('validated_at');
        }

        return $query->latest()->limit(50)->get();
    }

    /**
     * Ringkasan untuk kepala halaman peninjauan.
     *
     * @return array{total: int, validated: int, correct: int, incorrect: int}
     */
    public function stats(): array
    {
        // Konsisten dengan listForReview: hanya sesi yang punya hasil diagnosa.
        /** @var callable(Builder): Builder $base */
        $base = fn ($q) => $q->has('diagnoses');

        return [
            'total' => (int) $base(ConsultationSession::query())->count(),
            'validated' => (int) $base(ConsultationSession::query())->whereNotNull('validated_at')->count(),
            'correct' => (int) $base(ConsultationSession::query())->where('validation', ConsultationSession::VALIDATION_CORRECT)->count(),
            'incorrect' => (int) $base(ConsultationSession::query())->where('validation', ConsultationSession::VALIDATION_INCORRECT)->count(),
        ];
    }

    /**
     * Tambah diagnosa manual untuk seorang pasien oleh dokter.
     *
     * @param  array{patient_id: int, disease_id: int, symptoms?: array<int, int>, doctor_note?: string|null}  $data
     */
    public function storeManual(int $doctorId, array $data): ConsultationSession
    {
        $disease = Disease::findOrFail($data['disease_id']);
        $symptomIds = array_map('intval', $data['symptoms'] ?? []);

        return DB::transaction(function () use ($doctorId, $disease, $symptomIds, $data) {
            $session = ConsultationSession::create([
                'user_id' => $data['patient_id'],
                'doctor_id' => $doctorId,
                'status' => ConsultationSession::STATUS_DIAGNOSED,
                'has_red_flag' => false,
                'source' => ConsultationSession::SOURCE_MANUAL,
                'doctor_note' => $data['doctor_note'] ?? null,
                'validated_at' => now(),
            ]);

            $session->symptoms()->sync($symptomIds);

            Diagnosis::create([
                'consultation_session_id' => $session->id,
                'disease_id' => $disease->id,
                'confidence' => 100.0,
                'breakdown' => [],
                'total_weight' => 0,
                'rank' => 1,
            ]);

            return $session;
        });
    }

    /**
     * Simpan penilaian dokter atas satu hasil diagnosa.
     *
     * @param  array{validation: string, doctor_note?: string|null}  $data
     */
    public function validate(ConsultationSession $session, int $doctorId, array $data): void
    {
        $session->update([
            'doctor_id' => $doctorId,
            'validation' => $data['validation'],
            'doctor_note' => $data['doctor_note'] ?? null,
            'validated_at' => now(),
        ]);
    }
}