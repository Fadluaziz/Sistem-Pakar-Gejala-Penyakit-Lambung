<?php

namespace App\Features\DiseaseDetection\Requests;

use App\Features\DiseaseDetection\Models\Disease;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form tambah diagnosa manual oleh dokter.
 */
class StoreManualDiagnosisRequest extends FormRequest
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
            'patient_id' => ['required', Rule::exists('users', 'id')->where('role', 'patient')],
            'disease_id' => ['required', Rule::exists('diseases', 'id')],
            'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['integer', Rule::exists('symptoms', 'id')],
            'doctor_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Pilih pasien terlebih dahulu.',
            'patient_id.exists' => 'Pasien yang dipilih tidak valid.',
            'disease_id.required' => 'Pilih penyakit terlebih dahulu.',
            'disease_id.exists' => 'Penyakit yang dipilih tidak valid.',
            'symptoms.array' => 'Format gejala tidak valid.',
            'symptoms.*.integer' => 'Gejala yang dipilih tidak valid.',
            'symptoms.*.exists' => 'Gejala yang dipilih tidak dikenali.',
            'doctor_note.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}