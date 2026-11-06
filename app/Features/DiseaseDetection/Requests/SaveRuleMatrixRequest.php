<?php

namespace App\Features\DiseaseDetection\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveRuleMatrixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['editor', 'admin'], true);
    }

    /**
     * Sel matriks dikirim sebagai weights[disease_id][symptom_id]. Sel yang
     * dikosongkan menjadi null (ConvertEmptyStringsToNull) dan berarti "hapus".
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'weights' => ['nullable', 'array'],
            'weights.*' => ['nullable', 'array'],
            'weights.*.*' => ['nullable', 'integer', 'min:0', 'max:5'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'weights.*.*.integer' => 'Bobot harus berupa angka.',
            'weights.*.*.min' => 'Bobot minimal 0.',
            'weights.*.*.max' => 'Bobot maksimal 5.',
        ];
    }

    /**
     * @return array<int|string, array<int|string, int|string|null>>
     */
    public function weights(): array
    {
        return $this->validated()['weights'] ?? [];
    }
}
