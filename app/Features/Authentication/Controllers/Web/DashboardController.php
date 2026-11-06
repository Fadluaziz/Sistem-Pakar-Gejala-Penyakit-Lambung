<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use App\Features\Shared\Services\DashboardStatsService;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function __construct(
        private readonly DashboardStatsService $stats,
    ) {
        parent::__construct(app(AuthService::class));
    }

    public function patientDashboard(): View
    {
        return $this->dashboard();
    }

    public function doctorDashboard(): View
    {
        return $this->dashboard();
    }

    public function editorDashboard(): View
    {
        return $this->dashboard();
    }

    public function adminDashboard(): View
    {
        return $this->dashboard();
    }

    /**
     * Dashboard bersama; ringkasan angka disesuaikan dengan peran pengguna
     * yang sedang login (bukan dari route yang diakses).
     */
    private function dashboard(): View
    {
        return view('features.Authentication.dashboard', [
            'stats' => $this->stats->forUser(auth()->user()),
        ]);
    }
}
