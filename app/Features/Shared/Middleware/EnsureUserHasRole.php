<?php

namespace App\Features\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi akses route berdasarkan kolom `users.role`.
 *
 * Dipakai di routes_web.php sebagai `role:patient,doctor,admin`.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Memeriksa role menggunakan sistem Spatie. Fallback ke kolom `role`
        // jika role Spatie belum tersinkronisasi (misal saat testing).
        $hasAccess = $roles === [] || $user->hasAnyRole($roles) || in_array($user->role, $roles, true);

        if (! $hasAccess) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
