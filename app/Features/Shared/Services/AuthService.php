<?php

namespace App\Features\Shared\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Attempt to log the user in.
     *
     * @param  array{credential: string, password: string}  $credentials
     * @return array{status: bool, message: string, redirect?: string}
     */
    public function login(array $credentials): array
    {
        $credential = $credentials['credential'];
        $password    = $credentials['password'];

        $user = $this->findUserByCredential($credential);

        if (! $user || ! Hash::check($password, $user->password)) {
            return ['status' => false, 'message' => 'Credential atau password salah.'];
        }

        Auth::login($user);

        return [
            'status'   => true,
            'message'  => 'Berhasil masuk.',
            'redirect' => $this->redirectByRole($user->role),
        ];
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Register a new user and log them in automatically.
     *
     * @param  array{name: string, email: string, password: string, role: string}  $data
     * @return array{status: bool, message: string, redirect?: string}
     */
    public function register(array $data): array
    {
        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            return ['status' => false, 'message' => 'Email sudah terdaftar.'];
        }

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => $data['role'] ?? 'patient',
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        return [
            'status'   => true,
            'message'  => 'Pendaftaran berhasil. Selamat datang!',
            'redirect' => $this->redirectByRole($user->role),
        ];
    }

    /**
     * Find user by email or username.
     */
    private function findUserByCredential(string $credential): ?User
    {
        return User::where('email', $credential)
            ->orWhere('username', $credential)
            ->first();
    }

    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'admin'  => '/admin/dashboard',
            'doctor' => '/doctor/dashboard',
            'editor' => '/editor/dashboard',
            default  => '/patient/dashboard',
        };
    }
}
