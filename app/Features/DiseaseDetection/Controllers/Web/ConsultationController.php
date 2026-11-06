<?php

namespace App\Features\DiseaseDetection\Controllers\Web;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Requests\ConsultationStepRequest;
use App\Features\DiseaseDetection\Services\ConsultationService;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConsultationController extends BaseController
{
    /**
     * Draft jawaban lintas langkah disimpan di session.
     */
    private const DRAFT_KEY = 'detection.consultation.answers';

    public function __construct(
        private readonly ConsultationService $consultation,
    ) {
        parent::__construct(app(AuthService::class));
    }

    /**
     * Halaman edukasi: mengenalkan jenis-jenis penyakit lambung sebelum konsultasi.
     */
    public function diseases(): View
    {
        return view('features.DiseaseDetection.diseases', [
            'diseases' => $this->consultation->diseasePreview(),
        ]);
    }

    /**
     * Mulai konsultasi baru: buang draft lama lalu masuk ke langkah pertama.
     */
    public function index(): RedirectResponse
    {
        session()->forget(self::DRAFT_KEY);

        return redirect()->route('detection.consultation.show', 'utama');
    }

    /**
     * Tampilkan satu langkah berdasarkan draft yang sudah tersimpan.
     */
    public function show(string $step): View|RedirectResponse
    {
        if (! $this->stepIsApplicable($step)) {
            return redirect()->route('detection.consultation');
        }

        return $this->render($step);
    }

    /**
     * Simpan jawaban satu langkah, lalu lanjut ke langkah berikutnya atau
     * selesaikan konsultasi bila sudah di langkah terakhir.
     */
    public function save(ConsultationStepRequest $request, string $step): RedirectResponse
    {
        if (! $this->stepIsApplicable($step)) {
            return redirect()->route('detection.consultation');
        }

        $draft = $this->consultation->saveStep($step, $request->symptomIds(), $this->draft());
        session([self::DRAFT_KEY => $draft]);

        // Percabangan dihitung ulang setelah jawaban tersimpan: memilih gejala
        // baru bisa menambah atau menghapus langkah berikutnya.
        $steps = $this->consultation->applicableSteps($draft);
        $position = array_search($step, $steps, true);
        $next = $position === false ? null : ($steps[$position + 1] ?? null);

        if ($next !== null) {
            return redirect()->route('detection.consultation.show', $next);
        }

        $session = $this->consultation->finish($draft, (int) auth()->id());
        session()->forget(self::DRAFT_KEY);

        return redirect()->route('detection.result', $session)
            ->with('success', 'Konsultasi selesai. Berikut hasilnya.');
    }

    /**
     * Tampilkan hasil satu sesi konsultasi.
     */
    public function result(ConsultationSession $consultationSession): View
    {
        abort_unless((int) $consultationSession->user_id === (int) auth()->id(), 403);

        $consultationSession->load(['symptoms', 'diagnoses.disease']);

        return view('features.DiseaseDetection.result', [
            'session' => $consultationSession,
            'diagnoses' => $consultationSession->diagnoses->sortBy('rank')->values(),
        ]);
    }

    /**
     * Halaman cetak hasil diagnosa (template PDF/A4).
     */
    public function printResult(ConsultationSession $consultationSession): View
    {
        abort_unless((int) $consultationSession->user_id === (int) auth()->id(), 403);

        $consultationSession->load(['user', 'symptoms', 'diagnoses.disease', 'doctor']);

        return view('features.DiseaseDetection.print', [
            'session' => $consultationSession,
            'diagnoses' => $consultationSession->diagnoses->sortBy('rank')->values(),
        ]);
    }

    /**
     * Riwayat konsultasi milik pasien yang sedang login.
     */
    public function history(): View
    {
        $sessions = ConsultationSession::query()
            ->where('user_id', auth()->id())
            ->with(['diagnoses' => fn ($query) => $query->orderBy('rank')])
            ->withCount('symptoms')
            ->latest()
            ->paginate(10);

        return view('features.DiseaseDetection.history', [
            'sessions' => $sessions,
        ]);
    }

    private function stepIsApplicable(string $step): bool
    {
        return in_array($step, $this->consultation->applicableSteps($this->draft()), true);
    }

    private function render(string $step): View
    {
        $draft = $this->draft();
        $steps = $this->consultation->applicableSteps($draft);
        $position = array_search($step, $steps, true);
        $lastIndex = count($steps) - 1;

        return view('features.DiseaseDetection.consultation', [
            'step' => $this->consultation->step($step),
            'questions' => $this->consultation->questionsForStep($step),
            'selectedIds' => $draft,
            'stepNumber' => $position === false ? 1 : $position + 1,
            'totalSteps' => count($steps),
            'previousStep' => ($position === false || $position === 0) ? null : $steps[$position - 1],
            'isLastStep' => $position === false || $position === $lastIndex,
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function draft(): array
    {
        $draft = session(self::DRAFT_KEY, []);

        return is_array($draft) ? array_map('intval', $draft) : [];
    }
}
