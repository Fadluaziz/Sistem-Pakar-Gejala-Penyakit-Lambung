<?php

namespace App\Features\Authentication\Controllers\Web;

use App\Features\Authentication\Requests\UpdateProfileRequest;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(AuthService::class));
    }

    /**
     * Kolom data diri yang dihitung untuk indikator kelengkapan profil.
     */
    private const PROFILE_FIELDS = [
        'nik',
        'birth_date',
        'gender',
        'phone',
        'address',
        'occupation',
        'blood_type',
    ];

    /**
     * Halaman profil pasien: ringkasan profil lengkap + form untuk mengubahnya.
     */
    public function edit(): View
    {
        $user = auth()->user();

        $filled = collect(self::PROFILE_FIELDS)
            ->filter(fn (string $field): bool => filled($user->{$field}))
            ->count();
        $total = count(self::PROFILE_FIELDS);

        return view('features.Authentication.profile', [
            'user' => $user,
            'completeness' => [
                'filled' => $filled,
                'total' => $total,
                'percent' => (int) round($filled / $total * 100),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        auth()->user()->update($request->validated());

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
