<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa skema https untuk seluruh URL bila FORCE_HTTPS=true. Ini
        // melengkapi trustProxies: header proxy membuat Laravel tahu request
        // aslinya https, sedangkan opsi ini memaksa skema secara eksplisit.
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Tautan di email reset password diarahkan ke route aplikasi ini
        // (bukan halaman bawaan Laravel yang tidak tersedia di proyek ini).
        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });
    }
}
