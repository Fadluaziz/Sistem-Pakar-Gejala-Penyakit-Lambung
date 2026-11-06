<?php

namespace App\Features\DiseaseDetection\Requests;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['doctor', 'admin'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'validation' => [
                'required',
                Rule::in([
                    ConsultationSession::VALIDATION_CORRECT,
                    ConsultationSession::VALIDATION_INCORRECT,
                ]),
            ],
            'doctor_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'validation.required' => 'Pilih penilaian benar atau salah.',
            'validation.in' => 'Penilaian yang dipilih tidak valid.',
            'doctor_note.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}