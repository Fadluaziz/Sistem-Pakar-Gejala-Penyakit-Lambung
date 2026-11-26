<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\DiseaseDetection\Models\ConsultationSession;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use App\Features\Shared\Services\DashboardStatsService;
use App\Models\User;
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
        $user = auth()->user();
        $data = [
            'stats' => $this->stats->forUser($user),
        ];

        // Data aktivitas terbaru hanya untuk Admin
        if ($user->hasRole('admin')) {
            $data['recentUsers'] = User::latest()->take(5)->get();
            $data['recentConsultations'] = ConsultationSession::with(['user', 'diagnoses.disease'])
                ->latest()
                ->take(5)
                ->get();
        }

        return view('features.Authentication.dashboard', $data);
    }
}
