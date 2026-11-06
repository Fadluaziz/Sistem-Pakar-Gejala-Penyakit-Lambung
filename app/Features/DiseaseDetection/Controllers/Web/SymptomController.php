<?php

namespace App\Features\DiseaseDetection\Controllers\Web;

use App\Features\DiseaseDetection\Models\Symptom;
use App\Features\DiseaseDetection\Requests\SymptomRequest;
use App\Features\DiseaseDetection\Services\KnowledgeBaseService;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SymptomController extends BaseController
{
    public function __construct(
        private readonly KnowledgeBaseService $knowledgeBase,
    ) {
        parent::__construct(app(AuthService::class));
    }

    public function index(): View
    {
        return view('features.DiseaseDetection.editor_symptoms', [
            'symptoms' => Symptom::orderBy('code')->get(),
            'usedIds' => $this->knowledgeBase->usedSymptomIds(),
        ]);
    }

    public function store(SymptomRequest $request): RedirectResponse
    {
        $this->knowledgeBase->createSymptom($request->validated());

        return redirect()->route('editor.symptoms.index')
            ->with('success', 'Gejala berhasil ditambahkan.');
    }

    public function update(SymptomRequest $request, Symptom $symptom): RedirectResponse
    {
        $this->knowledgeBase->updateSymptom($symptom, $request->validated());

        return redirect()->route('editor.symptoms.index')
            ->with('success', 'Gejala berhasil diperbarui.');
    }

    public function destroy(Symptom $symptom): RedirectResponse
    {
        if (! $this->knowledgeBase->deleteSymptom($symptom)) {
            return redirect()->route('editor.symptoms.index')
                ->with('error', 'Gejala ini sudah dipakai pada riwayat konsultasi sehingga tidak dapat dihapus.');
        }

        return redirect()->route('editor.symptoms.index')
            ->with('success', 'Gejala berhasil dihapus.');
    }
}
