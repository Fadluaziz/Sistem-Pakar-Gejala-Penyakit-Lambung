<?php

use Illuminate\Support\Facades\Route;
use App\Features\Authentication\Controllers\Web\LoginController;
use App\Features\Authentication\Controllers\Web\RegisterController;
use App\Features\Authentication\Controllers\Web\DashboardController;
use App\Features\Authentication\Controllers\Web\ProfileController;

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

// Profil pasien: data diri yang ikut tercetak pada hasil diagnosa.
Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');
});
