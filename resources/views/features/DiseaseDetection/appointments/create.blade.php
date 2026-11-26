@extends('layouts.app')

@section('title', 'Buat Janji Temu')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Buat Janji Temu</h2>
                <p class="muted" style="margin-top: .25rem;">Jadwalkan konsultasi tatap muka dengan dokter di Puskesmas.</p>
            </div>
            <a href="{{ route('detection.appointments.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>{{ $errors->first() }}</strong>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('detection.appointments.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="appointment_date">Tanggal Kedatangan</label>
                    <input type="date" id="appointment_date" name="appointment_date"
                           value="{{ old('appointment_date') }}" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label for="appointment_time">Jam Kedatangan</label>
                    <input type="time" id="appointment_time" name="appointment_time"
                           value="{{ old('appointment_time') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="complaint">Keluhan Singkat</label>
                <textarea id="complaint" name="complaint" placeholder="Tuliskan keluhan utama atau hasil diagnosa dari sistem pakar..." required>{{ old('complaint') }}</textarea>
            </div>

            <div class="actions">
                <span class="muted"></span>
                <button type="submit" class="btn btn-primary">Ajukan Janji Temu</button>
            </div>
        </form>
    </div>
@endsection
