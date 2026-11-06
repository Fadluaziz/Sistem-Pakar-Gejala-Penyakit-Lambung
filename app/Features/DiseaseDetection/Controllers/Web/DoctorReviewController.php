<?php

namespace App\Features\DiseaseDetection\Controllers\Web;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Symptom;
use App\Features\DiseaseDetection\Requests\StoreManualDiagnosisRequest;
use App\Features\DiseaseDetection\Requests\ValidateDiagnosisRequest;
use App\Features\DiseaseDetection\Services\DoctorValidationService;
use App\Models\User;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DoctorReviewController extends BaseController
{
    public function __construct(
        private readonly DoctorValidationService $validation,
    ) {
        parent::__construct(app(AuthService::class));
    }

    /**
     * Daftar hasil diagnosa untuk ditinjau dokter.
     */
    public function index(string $filter = 'all'): View
    {
        $filter = in_array($filter, ['all', 'validated', 'unvalidated'], true) ? $filter : 'all';

        return view('features.DiseaseDetection.doctor_review', [
            'sessions' => $this->validation->listForReview($filter),
            'filter' => $filter,
            'stats' => $this->validation->stats(),
        ]);
    }

    /**
     * Detail satu hasil diagnosa untuk ditinjau dokter, lengkap dengan
     * jejak penalaran.
     *
     * Nama parameter method WAJIB sama dengan nama parameter route
     * ({consultationSession}) agar route model binding menyuntikkan model yang
     * sebenarnya; bila nama berbeda Laravel meng-inject model kosong (id = null).
     */
    public function show(ConsultationSession $consultationSession): View
    {
        $consultationSession->load(['diagnoses.disease', 'symptoms', 'user', 'doctor']);

        return view('features.DiseaseDetection.doctor_show', [
            'session' => $consultationSession,
            'diagnoses' => $consultationSession->diagnoses->sortBy('rank')->values(),
        ]);
    }

    /**
     * Form tambah diagnosa manual untuk pasien.
     */
    public function createManual(): View
    {
        return view('features.DiseaseDetection.doctor_manual', [
            'patients' => User::query()
                ->where('role', 'patient')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'diseases' => Disease::orderBy('code')->get(),
            'symptoms' => Symptom::orderBy('code')->get(),
        ]);
    }

    /**
     * Simpan diagnosa manual.
     */
    public function storeManual(StoreManualDiagnosisRequest $request): RedirectResponse
    {
        $session = $this->validation->storeManual((int) auth()->id(), $request->validated());

        return redirect()->route('doctor.review.show', $session->id)
            ->with('success', 'Diagnosa berhasil ditambahkan.');
    }

    /**
     * Simpan penilaian dokter atas satu hasil diagnosa.
     */
    public function store(ValidateDiagnosisRequest $request, ConsultationSession $consultationSession): RedirectResponse
    {
        $this->validation->validate($consultationSession, (int) auth()->id(), $request->validated());

        return redirect()->route('doctor.review.show', $consultationSession->id)
            ->with('success', 'Penilaian disimpan.');
    }

    /**
     * Hapus penilaian dokter sehingga hasil kembali belum divalidasi.
     */
    public function reset(ConsultationSession $consultationSession): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['doctor', 'admin'], true), 403);

        $consultationSession->update([
            'doctor_id' => null,
            'validation' => null,
            'doctor_note' => null,
            'validated_at' => null,
        ]);

        return redirect()->route('doctor.review.show', $consultationSession->id)
            ->with('success', 'Penilaian dibatalkan.');
    }
}