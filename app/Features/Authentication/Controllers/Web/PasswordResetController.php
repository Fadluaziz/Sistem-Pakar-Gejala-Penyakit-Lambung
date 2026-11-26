<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Alur lupa password memakai fitur bawaan Laravel (password broker),
 * dikemas dengan tampilan dan pesan berbahasa Indonesia.
 */
class PasswordResetController
{
    /**
     * Formulir memasukkan email untuk menerima tautan reset.
     */
    public function requestForm(): View
    {
        return view('features.Authentication.forgot-password');
    }

    /**
     * Kirim tautan reset ke email yang terdaftar.
     */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
            ],
        );

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', $this->message($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $this->message($status)]);
    }

    /**
     * Formulir memasukkan password baru (dibuka dari tautan di email).
     */
    public function resetForm(Request $request, string $token): View
    {
        return view('features.Authentication.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * Simpan password baru bila token valid.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password baru wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ],
        );

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill(['password' => Hash::make($password)])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', $this->message($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $this->message($status)]);
    }

    /**
     * Terjemahan status password broker ke bahasa Indonesia.
     */
    private function message(string $status): string
    {
        return match ($status) {
            Password::RESET_LINK_SENT => 'Tautan reset password sudah dikirim. Silakan periksa email Anda.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Coba lagi beberapa saat lagi.',
            Password::INVALID_USER => 'Email tersebut tidak terdaftar pada sistem.',
            Password::INVALID_TOKEN => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            Password::PASSWORD_RESET => 'Password berhasil diubah. Silakan masuk dengan password baru.',
            default => 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }
}
