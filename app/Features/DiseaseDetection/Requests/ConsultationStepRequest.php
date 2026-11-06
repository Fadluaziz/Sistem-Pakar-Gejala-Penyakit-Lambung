<?php

namespace App\Features\DiseaseDetection\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultationStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Pasien boleh tidak memilih gejala apa pun pada satu langkah, jadi
     * `symptoms` bersifat nullable; yang penting setiap nilai yang dikirim valid.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['integer', Rule::exists('symptoms', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symptoms.array' => 'Format pilihan gejala tidak valid.',
            'symptoms.*.integer' => 'Pilihan gejala tidak valid.',
            'symptoms.*.exists' => 'Gejala yang dipilih tidak dikenali.',
        ];
    }

    /**
     * Id gejala yang dicentang pasien.
     *
     * @return array<int, int>
     */
    public function symptomIds(): array
    {
        return array_map('intval', $this->validated()['symptoms'] ?? []);
    }
}
