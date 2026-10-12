<?php

namespace App\Features\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credential' => 'required|string|min:3',
            'password'   => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'credential.required' => 'Email atau username wajib diisi.',
            'credential.min'      => 'Email atau username minimal 3 karakter.',
            'password.required'   => 'Password wajib diisi.',
            'password.min'        => 'Password minimal 6 karakter.',
        ];
    }
}
