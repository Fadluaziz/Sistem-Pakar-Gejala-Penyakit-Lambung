<?php

namespace App\Features\Shared\Controllers;

use App\Features\Shared\Services\AuthService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    protected function redirectTo(): string
    {
        $role = Auth::user()?->role;

        return match ($role) {
            'admin' => '/admin/dashboard',
            'doctor' => '/doctor/dashboard',
            'editor' => '/editor/dashboard',
            default => '/patient/dashboard',
        };
    }
}
