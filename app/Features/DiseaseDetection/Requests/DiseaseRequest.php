<?php

namespace App\Features\DiseaseDetection\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiseaseRequest extends FormRequest
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
                Rule::unique('diseases', 'code')->ignore($this->route('disease')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'prevention' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode penyakit wajib diisi.',
            'code.unique' => 'Kode penyakit sudah dipakai.',
            'code.max' => 'Kode penyakit maksimal 10 karakter.',
            'name.required' => 'Nama penyakit wajib diisi.',
            'name.max' => 'Nama penyakit maksimal 255 karakter.',
            'summary.max' => 'Ringkasan maksimal 500 karakter.',
        ];
    }
}
