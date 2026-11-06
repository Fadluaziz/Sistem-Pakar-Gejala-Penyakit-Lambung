@extends('layouts.app')

@section('title', 'Tambah Diagnosa Manual')

@section('content')
    <div class="card">
        <h2>Tambah Diagnosa Manual</h2>
        <p class="muted" style="margin-top: .35rem;">
            Tambahkan hasil diagnosa untuk seorang pasien tanpa lewat konsultasi — misalnya
            saat pasien datang langsung dengan hasil pemeriksaan. Diagnosa ini akan muncul
            di riwayat pasien yang bersangkutan.
        </p>

        @if ($errors->any())
            <div class="alert alert-error" style="margin-top: 1rem;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('doctor.review.store') }}" style="margin-top: 1.25rem;">
            @csrf

            <div class="form-group">
                <label for="patient_id">Pasien</label>
                <select id="patient_id" name="patient_id" required>
                    <option value="" {{ old('patient_id') ? '' : 'selected' }} disabled>— Pilih pasien —</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" {{ (string) old('patient_id') === (string) $patient->id ? 'selected' : '' }}>
                            {{ $patient->name }} ({{ $patient->email }})
                        </option>
                    @endforeach
                </select>
                @if (empty($patients))
                    <p class="hint">Belum ada pasien terdaftar. Pasien bisa mendaftar lewat halaman daftar.</p>
                @endif
            </div>

            <div class="form-group">
                <label for="disease_id">Penyakit</label>
                <select id="disease_id" name="disease_id" required>
                    <option value="" {{ old('disease_id') ? '' : 'selected' }} disabled>— Pilih penyakit —</option>
                    @foreach ($diseases as $disease)
                        <option value="{{ $disease->id }}" {{ (string) old('disease_id') === (string) $disease->id ? 'selected' : '' }}>
                            {{ $disease->code }} — {{ $disease->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Gejala yang sesuai (opsional)</label>
                <div class="manual-symptoms">
                    @foreach ($symptoms as $symptom)
                        <label class="symptom-item manual-symptom">
                            <input type="checkbox" name="symptoms[]" value="{{ $symptom->id }}"
                                   @checked(in_array($symptom->id, old('symptoms', []), true))>
                            <span><span class="code-badge">{{ $symptom->code }}</span>{{ $symptom->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="doctor_note">Catatan (opsional)</label>
                <textarea id="doctor_note" name="doctor_note" placeholder="Catatan medis singkat...">{{ old('doctor_note') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Diagnosa</button>
        </form>

        <div class="actions" style="justify-content: flex-start;">
            <a href="{{ route('doctor.review.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>
    </div>
@endsection

@section('styles')
    .hint { margin-top: .35rem; font-size: .82rem; color: #c62828; }
    .manual-symptoms {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .5rem;
        max-height: 260px; overflow-y: auto; padding: .25rem;
    }
    .manual-symptom { margin: 0; padding: .5rem .7rem; font-size: .85rem; }
@endsection