<?php

namespace App\Features\Shared\Services;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Rule;
use App\Features\DiseaseDetection\Models\Symptom;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Ringkasan angka untuk halaman dashboard, disesuaikan per peran pengguna.
 *
 * Diletakkan di Shared karena mengagregasi data lintas fitur (pengguna,
 * konsultasi, dan basis aturan) dan dipakai halaman dashboard.
 */
class DashboardStatsService
{
    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    public function forUser(User $user): array
    {
        return match ($user->role) {
            'doctor' => $this->doctorStats(),
            'admin' => $this->adminStats(),
            'editor' => $this->editorStats(),
            default => $this->patientStats($user),
        };
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    private function patientStats(User $user): array
    {
        $total = ConsultationSession::where('user_id', $user->id)->count();
        $last = ConsultationSession::where('user_id', $user->id)->latest()->first();

        $lastResult = '—';
        if ($last !== null) {
            $top = $last->diagnoses()->where('rank', 1)->with('disease')->first();
            $lastResult = $top?->disease !== null
                ? Str::before($top->disease->name, ' (')
                : 'Belum disimpulkan';
        }

        return [
            [
                'label' => 'Total Konsultasi',
                'value' => (string) $total,
                'hint' => $total > 0 ? 'kali Anda berkonsultasi' : 'belum ada konsultasi',
            ],
            [
                'label' => 'Konsultasi Terakhir',
                'value' => $last?->created_at?->locale('id')->isoFormat('D MMM YYYY') ?? '—',
                'hint' => $last !== null ? 'terakhir dilakukan' : 'belum ada',
            ],
            [
                'label' => 'Hasil Terakhir',
                'value' => $lastResult,
                'hint' => $last !== null ? 'diagnosa terakhir' : 'belum ada',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    private function doctorStats(): array
    {
        return [
            [
                'label' => 'Perlu Ditinjau',
                'value' => (string) ConsultationSession::has('diagnoses')->whereNull('validated_at')->count(),
                'hint' => 'belum divalidasi',
            ],
            [
                'label' => 'Total Diagnosa',
                'value' => (string) ConsultationSession::has('diagnoses')->count(),
                'hint' => 'tersimpan di sistem',
            ],
            [
                'label' => 'Sudah Divalidasi',
                'value' => (string) ConsultationSession::whereNotNull('validated_at')->count(),
                'hint' => 'selesai ditinjau',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    private function editorStats(): array
    {
        return [
            ['label' => 'Penyakit', 'value' => (string) Disease::count(), 'hint' => 'dalam basis data'],
            ['label' => 'Gejala', 'value' => (string) Symptom::count(), 'hint' => 'terdaftar'],
            ['label' => 'Aturan', 'value' => (string) Rule::count(), 'hint' => 'premis berbobot'],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    private function adminStats(): array
    {
        return [
            ['label' => 'Pengguna', 'value' => (string) User::count(), 'hint' => 'terdaftar'],
            ['label' => 'Konsultasi', 'value' => (string) ConsultationSession::count(), 'hint' => 'total tercatat'],
            [
                'label' => 'Perlu Ditinjau',
                'value' => (string) ConsultationSession::has('diagnoses')->whereNull('validated_at')->count(),
                'hint' => 'belum divalidasi',
            ],
        ];
    }
}
