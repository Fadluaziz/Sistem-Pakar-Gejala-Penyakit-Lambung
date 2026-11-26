<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Authentication\Requests\LoginRequest;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;

class LoginController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(AuthService::class));
    }

    public function showLogin()
    {
        if (auth()->check()) {
            return redirect($this->redirectTo());
        }

        return view('features.Authentication.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result['status']) {
            return back()->withErrors(['login' => $result['message']])
                ->withInput($request->only('credential'));
        }

        return redirect($result['redirect'])->with('success', $result['message']);
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect('/login')->with('success', 'Berhasil keluar.');
    }
}
