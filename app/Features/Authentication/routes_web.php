<?php

use Illuminate\Support\Facades\Route;
use App\Features\Authentication\Controllers\Web\LoginController;
use App\Features\Authentication\Controllers\Web\RegisterController;
use App\Features\Authentication\Controllers\Web\DashboardController;

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
