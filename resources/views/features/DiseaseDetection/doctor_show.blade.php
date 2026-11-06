@extends('layouts.app')

@section('title', 'Tinjau Hasil Diagnosa')

@section('content')
    @php
        $primary = $diagnoses->firstWhere('rank', 1);
    @endphp

    <div class="card">
        <div class="history-head">
            <div>
                <h2>Hasil Konsultasi</h2>
                <p class="muted" style="margin-top: .25rem;">
                    {{ $session->user?->name ?? 'Pengguna tidak dikenal' }} — {{ $session->created_at_label }}
                </p>
            </div>
            <div class="history-badges">
                @if ($session->has_red_flag)
                    <span class="pill pill-danger">Tanda bahaya</span>
                @endif
                <span class="pill">{{ $session->symptoms->count() }} gejala</span>
            </div>
        </div>
    </div>

    <div class="card {{ $session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_CORRECT ? 'status-correct' : ($session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_INCORRECT ? 'status-incorrect' : '') }}">
        <h2>{{ $primary?->disease->name ?? 'Belum dapat disimpulkan' }}
            @if ($primary)
                <span class="muted" style="font-size: .9rem;">— {{ number_format($primary->confidence, 0) }}%</span>
            @endif
        </h2>

        @if ($session->isValidated())
            <div class="pill {{ $session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_CORRECT ? 'pill-success' : 'pill-danger' }}" style="margin-top: .5rem;">
                Dinilai {{ $session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_CORRECT ? 'benar' : 'salah' }} oleh
                {{ $session->doctor?->name ?? 'Dokter' }}
            </div>
            @if ($session->doctor_note)
                <div class="doctor-note">
                    <div class="doctor-note-label">CATATAN DOKTER</div>
                    {{ $session->doctor_note }}
                </div>
            @endif
        @endif
    </div>

    <div class="card">
        <h2>Gejala yang Dipilih Pasien</h2>
        <div class="chip-list">
            @foreach ($session->symptoms as $symptom)
                <span class="chip"><span class="code-badge">{{ $symptom->code }}</span>{{ $symptom->name }}</span>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2>Bagaimana Kesimpulan Ini Diperoleh</h2>
        @if ($primary && ! empty($primary->breakdown))
            <div class="reason-list" style="margin-top: .75rem;">
                @foreach ($primary->breakdown as $reason)
                    <div class="reason-row">
                        <span><span class="code-badge">{{ $reason['code'] }}</span>{{ $reason['name'] }}</span>
                        <span class="weight-badge">bobot {{ $reason['weight'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="reason-total">
                Bobot terkumpul: <strong>{{ $primary->matchedWeight() }}</strong> dari
                <strong>{{ $primary->total_weight }}</strong> — keyakinan
                <strong>{{ number_format($primary->confidence, 2) }}%</strong>.
            </div>
        @else
            <p class="muted">Belum ada kesimpulan yang cukup untuk hasil ini.</p>
        @endif
    </div>

    @if ($session->isValidated())
        <div class="card">
            <h2>Penilaian Disimpan</h2>
            <p class="muted">
                Telah dinilai {{ $session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_CORRECT ? 'benar' : 'salah' }} pada
                {{ $session->validated_at?->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}.
            </p>

            <form method="POST" action="{{ route('doctor.review.reset', $session->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary" style="margin-top: 1rem;"
                        onclick="return confirm('Batalkan penilaian ini?')">
                    Batalkan Penilaian
                </button>
            </form>
        </div>
    @else
        <div class="card">
            <h2>Penilaian Dokter</h2>
            <form method="POST" action="{{ route('doctor.review.validate', $session->id) }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-error" style="margin-bottom: 1rem;">{{ $errors->first() }}</div>
                @endif

                <div class="review-label">Sejauh mana kesimpulan sistem sesuai penilaian Anda?</div>
                <div class="review-choice">
                    <input type="radio" id="validation-correct" name="validation"
                           value="{{ \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_CORRECT }}">
                    <label for="validation-correct">✓ Sesuai / Benar</label>

                    <input type="radio" id="validation-incorrect" name="validation"
                           value="{{ \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_INCORRECT }}">
                    <label for="validation-incorrect">✗ Kurang Sesuai</label>
                </div>

                <div class="review-label">Catatan / rekomendasi (opsional)</div>
                <div class="review-ta" style="margin-bottom: 1rem;">
                    <textarea name="doctor_note" placeholder="Contoh: kondisi pasien mengarah ke tukak, perlu endoskopi utk konfirmasi.">{{ old('doctor_note') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
            </form>
        </div>
    @endif

    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('doctor.review.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
    </div>
@endsection