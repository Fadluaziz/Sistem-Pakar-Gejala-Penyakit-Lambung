<?php

namespace App\Features\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'min:2', 'max:100'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'nik'        => ['nullable', 'digits:16'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender'     => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'phone'      => ['nullable', 'string', 'max:20'],
            'address'    => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.unique'      => 'Email sudah dipakai akun lain.',
            'nik.digits'        => 'NIK harus 16 digit angka.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'gender.in'         => 'Jenis kelamin tidak valid.',
            'blood_type.in'     => 'Golongan darah tidak valid.',
        ];
    }
}
