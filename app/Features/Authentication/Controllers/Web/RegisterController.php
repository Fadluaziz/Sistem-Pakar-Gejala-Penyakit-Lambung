<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Shared\Controllers\BaseController;
use App\Features\Authentication\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;

class RegisterController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(\App\Features\Shared\Services\AuthService::class));
    }

    /**
     * Halaman register kini menyatu di halaman login (panel "Daftar").
     * Setiap kunjungan/refresh ke /register diarahkan ke /login.
     */
    public function showRegister(): RedirectResponse
    {
        return redirect()->route('login');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $result = $this->authService->register([
            'name'                  => $request->input('name'),
            'email'                 => $request->input('email'),
            'password'              => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'role'                  => 'patient',
        ]);

        if (! $result['status']) {
            return back()->withErrors(['register' => $result['message']])
                         ->withInput();
        }

        return redirect($result['redirect'])->with('success', $result['message']);
    }
}
