<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Shared\Controllers\BaseController;
use App\Features\Authentication\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(\App\Features\Shared\Services\AuthService::class));
    }

    public function showRegister(): View
    {
        return view('features.Authentication.register');
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
