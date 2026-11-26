@extends('layouts.app')

@section('title', 'Janji Temu')

@section('content')
    @php $isManager = auth()->user()->hasPermissionTo('manage-appointments'); @endphp

    <div class="card">
        <div class="history-head">
            <div>
                <h2>{{ $isManager ? 'Manajemen Janji Temu' : 'Janji Temu Saya' }}</h2>
                <p class="muted" style="margin-top: .25rem;">
                    {{ $isManager ? 'Kelola jadwal kedatangan pasien ke Puskesmas.' : 'Pantau status pengajuan janji temu Anda.' }}
                </p>
            </div>
            @if(!$isManager)
                <a href="{{ route('detection.appointments.create') }}" class="btn btn-primary">+ Buat Janji Temu</a>
            @else
                <a href="{{ url('/' . auth()->user()->role . '/dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        @forelse($appointments as $appointment)
            <div class="review-item" style="border-top: 1px solid #f0f0f0; padding: 1.25rem 0;">
                <div class="history-head">
                    <div>
                        <div style="font-size: .85rem; color: #888;">
                            {{ $appointment->appointment_date->locale('id')->isoFormat('dddd, D MMMM YYYY') }} &middot; {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }} WIB
                        </div>
                        <div style="font-weight: 700; font-size: 1.1rem; margin-top: .25rem;">
                            @if($isManager)
                                Pasien: {{ $appointment->patient->name }}
                            @else
                                Kedatangan Anda
                            @endif
                        </div>
                        <div style="margin-top: .5rem; font-size: .9rem; color: #455a64;">
                            <strong>Keluhan:</strong> {{ $appointment->complaint }}
                        </div>
                    </div>
                    <div class="history-badges">
                        @if($appointment->status === 'pending')
                            <span class="pill" style="background:#fff3e0; color:#e65100;">Menunggu Konfirmasi</span>
                        @elseif($appointment->status === 'confirmed')
                            <span class="pill pill-success">Disetujui</span>
                        @elseif($appointment->status === 'completed')
                            <span class="pill" style="background:#e3f2fd; color:#1565c0;">Selesai</span>
                        @else
                            <span class="pill pill-danger">Dibatalkan</span>
                        @endif
                    </div>
                </div>

                @if($appointment->doctor_notes)
                    <div class="doctor-note" style="margin-top: 1rem;">
                        <div class="doctor-note-label">CATATAN DOKTER</div>
                        {{ $appointment->doctor_notes }}
                    </div>
                @endif

                @if($isManager)
                    <form method="POST" action="{{ route('detection.appointments.update_status', $appointment) }}" style="margin-top: 1rem; background: #f9f9f9; padding: 1rem; border-radius: 8px;">
                        @csrf
                        @method('PUT')
                        <div style="display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 200px;">
                                <label style="font-size: .8rem; font-weight: 700; color: #555;">Ubah Status</label>
                                <select name="status" style="width: 100%; padding: .5rem; border-radius: 6px; border: 1px solid #ccc; margin-top: .25rem;">
                                    <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Setujui</option>
                                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Batalkan</option>
                                </select>
                            </div>
                            <div style="flex: 2; min-width: 250px;">
                                <label style="font-size: .8rem; font-weight: 700; color: #555;">Catatan (Opsional)</label>
                                <input type="text" name="doctor_notes" value="{{ $appointment->doctor_notes }}" placeholder="Instruksi atau catatan..." style="width: 100%; padding: .5rem; border-radius: 6px; border: 1px solid #ccc; margin-top: .25rem;">
                            </div>
                            <div style="padding-top: 1.1rem;">
                                <button type="submit" class="btn btn-secondary" style="padding: .5rem 1rem;">Simpan</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: 2rem 0;">
                <p class="muted">Belum ada data janji temu.</p>
            </div>
        @endforelse

        <div class="pagination" style="margin-top: 1rem;">
            {{ $appointments->links() }}
        </div>
    </div>
@endsection
