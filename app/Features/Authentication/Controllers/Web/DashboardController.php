<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Shared\Controllers\BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(\App\Features\Shared\Services\AuthService::class));
    }

    public function patientDashboard(): View
    {
        return view('features.Authentication.dashboard');
    }

    public function doctorDashboard(): View
    {
        return view('features.Authentication.dashboard');
    }

    public function editorDashboard(): View
    {
        return view('features.Authentication.dashboard');
    }

    public function adminDashboard(): View
    {
        return view('features.Authentication.dashboard');
    }
}