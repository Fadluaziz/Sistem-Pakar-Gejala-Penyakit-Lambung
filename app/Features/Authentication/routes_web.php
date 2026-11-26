<?php

use App\Features\Authentication\Controllers\Web\DashboardController;
use App\Features\Authentication\Controllers\Web\LoginController;
use App\Features\Authentication\Controllers\Web\PasswordController;
use App\Features\Authentication\Controllers\Web\PasswordResetController;
use App\Features\Authentication\Controllers\Web\ProfileController;
use App\Features\Authentication\Controllers\Web\RegisterController;
use App\Features\Shared\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::get('/register', [RegisterController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'register'])
    ->name('register.submit');

// Lupa password: minta tautan, lalu buat password baru dari tautan email.
Route::middleware('guest')->group(function () {
    Route::get('/lupa-password', [PasswordResetController::class, 'requestForm'])
        ->name('password.request');

    Route::post('/lupa-password', [PasswordResetController::class, 'sendLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.store');
});

// Dashboard routes for each role
Route::middleware(['auth'])->group(function () {
    Route::get('/patient/dashboard', [DashboardController::class, 'patientDashboard'])
        ->name('patient.dashboard');
    Route::get('/doctor/dashboard', [DashboardController::class, 'doctorDashboard'])
        ->name('doctor.dashboard');
    Route::get('/editor/dashboard', [DashboardController::class, 'editorDashboard'])
        ->name('editor.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->name('admin.dashboard');
});

// Ubah password untuk pengguna yang sudah masuk (semua peran).
Route::middleware('auth')->group(function () {
    Route::get('/akun/password', [PasswordController::class, 'edit'])
        ->name('account.password.edit');

    Route::put('/akun/password', [PasswordController::class, 'update'])
        ->name('account.password.update');
});

// Manajemen Pengguna (Khusus Admin)
Route::middleware(['auth', 'permission:manage-users'])->prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
});

// Profil pasien: data diri yang ikut tercetak pada hasil diagnosa.
Route::middleware(['auth', 'permission:update-profile'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');
});
