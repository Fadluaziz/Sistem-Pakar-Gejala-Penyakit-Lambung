<?php

namespace App\Features\DiseaseDetection\Controllers\Web;

use App\Features\DiseaseDetection\Models\Disease;
use App\Features\DiseaseDetection\Requests\DiseaseRequest;
use App\Features\DiseaseDetection\Services\KnowledgeBaseService;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiseaseController extends BaseController
{
    public function __construct(
        private readonly KnowledgeBaseService $knowledgeBase,
    ) {
        parent::__construct(app(AuthService::class));
    }

    public function index(): View
    {
        return view('features.DiseaseDetection.editor_diseases', [
            'diseases' => Disease::orderBy('code')->get(),
            'usedIds' => $this->knowledgeBase->usedDiseaseIds(),
        ]);
    }

    public function store(DiseaseRequest $request): RedirectResponse
    {
        $this->knowledgeBase->createDisease($request->validated());

        return redirect()->route('editor.diseases.index')
            ->with('success', 'Penyakit berhasil ditambahkan.');
    }

    public function update(DiseaseRequest $request, Disease $disease): RedirectResponse
    {
        $this->knowledgeBase->updateDisease($disease, $request->validated());

        return redirect()->route('editor.diseases.index')
            ->with('success', 'Penyakit berhasil diperbarui.');
    }

    public function destroy(Disease $disease): RedirectResponse
    {
        if (! $this->knowledgeBase->deleteDisease($disease)) {
            return redirect()->route('editor.diseases.index')
                ->with('error', 'Penyakit ini sudah punya riwayat diagnosa sehingga tidak dapat dihapus.');
        }

        return redirect()->route('editor.diseases.index')
            ->with('success', 'Penyakit berhasil dihapus.');
    }
}
