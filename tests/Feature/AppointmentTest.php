<?php

namespace Tests\Feature;

use App\Features\DiseaseDetection\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_pasien_bisa_membuat_janji_temu(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->post(route('detection.appointments.store'), [
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'complaint' => 'Sakit perut bagian atas setelah makan.',
        ])->assertRedirect(route('detection.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'status' => 'pending',
        ]);
    }

    public function test_pasien_harus_mengisi_tanggal_dan_keluhan(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->post(route('detection.appointments.store'), [])
            ->assertSessionHasErrors(['appointment_date', 'appointment_time', 'complaint']);
    }

    public function test_dokter_bisa_mengubah_status_janji_temu(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = User::factory()->create(['role' => 'patient']);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00',
            'complaint' => 'Nyeri ulu hati',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctor)->put(route('detection.appointments.update_status', $appointment), [
            'status' => 'confirmed',
            'doctor_notes' => 'Silakan datang tepat waktu.',
        ])->assertRedirect(route('detection.appointments.index'));

        $this->assertSame('confirmed', $appointment->fresh()->status);
        $this->assertSame($doctor->id, $appointment->fresh()->doctor_id);
    }

    public function test_pasien_tidak_bisa_mengubah_status_janji_temu(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00',
            'complaint' => 'Nyeri ulu hati',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($patient)->put(route('detection.appointments.update_status', $appointment), [
            'status' => 'confirmed',
        ])->assertForbidden();
    }
}
