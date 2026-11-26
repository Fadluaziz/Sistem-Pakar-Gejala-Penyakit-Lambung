<?php

namespace App\Features\DiseaseDetection\Controllers\Web;

use App\Features\DiseaseDetection\Models\Appointment;
use App\Features\Shared\Controllers\BaseController;
use App\Features\Shared\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(AuthService::class));
    }

    public function index(): View
    {
        $user = auth()->user();

        if ($user->hasPermissionTo('manage-appointments')) {
            $appointments = Appointment::with(['patient', 'doctor'])->latest()->paginate(10);
        } else {
            $appointments = Appointment::where('patient_id', $user->id)->latest()->paginate(10);
        }

        return view('features.DiseaseDetection.appointments.index', compact('appointments'));
    }

    public function create(): View
    {
        return view('features.DiseaseDetection.appointments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'complaint' => 'required|string|max:1000',
        ], [
            'appointment_date.required' => 'Tanggal janji temu wajib diisi.',
            'appointment_date.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'appointment_time.required' => 'Jam janji temu wajib diisi.',
            'complaint.required' => 'Keluhan wajib diisi.',
        ]);

        Appointment::create([
            'patient_id' => auth()->id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'complaint' => $validated['complaint'],
            'status' => Appointment::STATUS_PENDING,
        ]);

        return redirect()->route('detection.appointments.index')
            ->with('success', 'Janji temu berhasil dibuat. Harap tunggu konfirmasi dari dokter/petugas.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-appointments'), 403);

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'doctor_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update([
            'status' => $request->status,
            'doctor_notes' => $request->doctor_notes,
            'doctor_id' => auth()->id(), // Otomatis set dokter yang menangani jika belum ada
        ]);

        return redirect()->route('detection.appointments.index')
            ->with('success', 'Status janji temu berhasil diperbarui.');
    }
}
