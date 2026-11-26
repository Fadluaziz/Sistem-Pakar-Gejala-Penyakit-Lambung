<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Authentication\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Ubah kata sandi untuk pengguna yang sedang login (semua peran).
 * Berbeda dengan lupa password yang memakai token lewat email, di sini
 * pengguna membuktikan identitasnya dengan password saat ini.
 */
class PasswordController
{
    public function edit(): View
    {
        return view('features.Authentication.account-password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()->route('account.password.edit')
            ->with('success', 'Password berhasil diubah.');
    }
}
