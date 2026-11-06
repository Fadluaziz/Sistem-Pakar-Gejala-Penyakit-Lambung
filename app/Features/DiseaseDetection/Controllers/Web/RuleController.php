<?php

namespace App\Features\DiseaseDetection\Controllers\Web;

use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Models\Symptom;
use App\Features\DiseaseDetection\Requests\SaveRuleMatrixRequest;
use App\Features\DiseaseDetection\Services\KnowledgeBaseService;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RuleController extends BaseController
{
    public function __construct(
        private readonly KnowledgeBaseService $knowledgeBase,
    ) {
        parent::__construct(app(AuthService::class));
    }

    /**
     * Matriks bobot aturan: baris = gejala, kolom = penyakit.
     */
    public function index(): View
    {
        return view('features.DiseaseDetection.editor_rules', [
            'symptoms' => Symptom::orderBy('code')->get(),
            'diseases' => Disease::orderBy('code')->get(),
            'matrix' => $this->knowledgeBase->weightMatrix(),
        ]);
    }

    /**
     * Simpan seluruh perubahan matriks bobot.
     */
    public function update(SaveRuleMatrixRequest $request): RedirectResponse
    {
        $this->knowledgeBase->saveMatrix($request->weights());

        return redirect()->route('editor.rules.index')
            ->with('success', 'Aturan berhasil disimpan.');
    }
}
