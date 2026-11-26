<?php

use App\Features\DiseaseDetection\Controllers\Web\AppointmentController;
use App\Features\DiseaseDetection\Controllers\Web\ConsultationController;
use App\Features\DiseaseDetection\Controllers\Web\DiseaseController;
use App\Features\DiseaseDetection\Controllers\Web\DoctorReviewController;
use App\Features\DiseaseDetection\Controllers\Web\RuleController;
use App\Features\DiseaseDetection\Controllers\Web\SymptomController;
use Illuminate\Support\Facades\Route;

// Panel editor: kelola matriks aturan, data gejala, dan data penyakit.
Route::middleware(['auth'])
    ->prefix('editor')
    ->name('editor.')
    ->group(function (): void {
        Route::middleware('permission:manage-rules')->group(function () {
            Route::get('aturan', [RuleController::class, 'index'])->name('rules.index');
            Route::put('aturan', [RuleController::class, 'update'])->name('rules.update');
        });

        Route::middleware('permission:manage-symptoms')->group(function () {
            Route::get('gejala', [SymptomController::class, 'index'])->name('symptoms.index');
            Route::post('gejala', [SymptomController::class, 'store'])->name('symptoms.store');
            Route::put('gejala/{symptom}', [SymptomController::class, 'update'])->name('symptoms.update');
            Route::delete('gejala/{symptom}', [SymptomController::class, 'destroy'])->name('symptoms.destroy');
        });

        Route::middleware('permission:manage-diseases')->group(function () {
            Route::get('penyakit', [DiseaseController::class, 'index'])->name('diseases.index');
            Route::post('penyakit', [DiseaseController::class, 'store'])->name('diseases.store');
            Route::put('penyakit/{disease}', [DiseaseController::class, 'update'])->name('diseases.update');
            Route::delete('penyakit/{disease}', [DiseaseController::class, 'destroy'])->name('diseases.destroy');
        });
    });

// Alur peninjauan & validasi hasil diagnosa oleh dokter.
Route::middleware(['auth'])
    ->prefix('dokter')
    ->name('doctor.')
    ->group(function (): void {
        Route::middleware('permission:review-consultations')->group(function () {
            Route::get('riwayat', [DoctorReviewController::class, 'index'])->name('review.index');
            Route::get('riwayat/filter/{filter}', [DoctorReviewController::class, 'index'])->name('review.filter');
            Route::get('hasil/{consultationSession}', [DoctorReviewController::class, 'show'])->name('review.show');
        });

        Route::middleware('permission:create-manual-diagnosis')->group(function () {
            Route::get('tambah-diagnosa', [DoctorReviewController::class, 'createManual'])->name('review.create');
            Route::post('tambah-diagnosa', [DoctorReviewController::class, 'storeManual'])->name('review.store');
        });

        Route::middleware('permission:validate-consultation')->group(function () {
            // Parameter rute WAJIB di posisi terakhir URI: route berparameter di tengah
            // (mis. hasil/{x}/validasi) gagal di-generate URL-nya dari dalam request aktif.
            Route::post('validasi/{consultationSession}', [DoctorReviewController::class, 'store'])->name('review.validate');
            Route::delete('validasi/{consultationSession}', [DoctorReviewController::class, 'reset'])->name('review.reset');
        });
    });

Route::middleware(['auth'])->group(function (): void {
    // Janji Temu (Booking ke Puskesmas)
    Route::middleware('permission:schedule-appointment|manage-appointments')->group(function () {
        Route::get('janji-temu', [AppointmentController::class, 'index'])->name('detection.appointments.index');
    });

    Route::middleware('permission:schedule-appointment')->group(function () {
        Route::get('janji-temu/buat', [AppointmentController::class, 'create'])->name('detection.appointments.create');
        Route::post('janji-temu', [AppointmentController::class, 'store'])->name('detection.appointments.store');
    });

    Route::middleware('permission:manage-appointments')->group(function () {
        Route::put('janji-temu/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('detection.appointments.update_status');
    });

    // Halaman edukasi: mengenal jenis penyakit lambung tanpa harus mulai konsultasi.
    Route::middleware('permission:access-education')->group(function () {
        Route::get('penyakit-lambung', [ConsultationController::class, 'diseases'])
            ->name('detection.diseases');
    });

    Route::middleware('permission:take-consultation')->prefix('konsultasi')->name('detection.')->group(function (): void {
        Route::get('/', [ConsultationController::class, 'index'])
            ->name('consultation');

        // Rute statis dan berparameter spesifik harus didaftarkan sebelum
        // rute `{step}` yang menangkap segmen apa pun.
        Route::get('riwayat', [ConsultationController::class, 'history'])
            ->name('history');

        Route::get('hasil/{consultationSession}', [ConsultationController::class, 'result'])
            ->name('result');

        Route::get('hasil/{consultationSession}/cetak', [ConsultationController::class, 'printResult'])
            ->name('result.print');

        Route::get('{step}', [ConsultationController::class, 'show'])
            ->name('consultation.show');

        Route::post('{step}', [ConsultationController::class, 'save'])
            ->name('consultation.save');
    });
});
