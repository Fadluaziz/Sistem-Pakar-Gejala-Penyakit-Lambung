<?php

namespace App\Features\DiseaseDetection\Requests;

use App\Features\DiseaseDetection\Models\Symptom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SymptomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['editor', 'admin'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:10',
                // Saat update, kode milik gejala ini sendiri dikecualikan.
                Rule::unique('symptoms', 'code')->ignore($this->route('symptom')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Symptom::CATEGORIES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode gejala wajib diisi.',
            'code.unique' => 'Kode gejala sudah dipakai.',
            'code.max' => 'Kode gejala maksimal 10 karakter.',
            'name.required' => 'Nama gejala wajib diisi.',
            'name.max' => 'Nama gejala maksimal 255 karakter.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
        ];
    }
}
