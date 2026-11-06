@extends('layouts.app')

@section('title', $step['title'].' – Konsultasi')

@section('content')
    <div class="card">
        <div class="progress-label">Bagian {{ $stepNumber }} dari {{ $totalSteps }}</div>
        <div class="progress-track">
            <div class="progress-fill" style="width: {{ (int) round($stepNumber / max($totalSteps, 1) * 100) }}%"></div>
        </div>

        <h2 style="margin-top: 1.25rem;">{{ $step['title'] }}</h2>
        <p class="muted" style="margin-top: .25rem;">{{ $step['description'] }}</p>

        @if ($errors->any())
            <div class="alert alert-error" style="margin-top: 1rem;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('detection.consultation.save', $step['slug']) }}">
            @csrf

            <div class="question-list">
                @foreach ($questions as $question)
                    @php $answered = in_array($question['id'], $selectedIds, true); @endphp

                    <div class="question-item" data-question>
                        <span class="question-text">
                            <span class="code-badge">{{ $question['code'] }}</span>{{ $question['question'] }}
                        </span>

                        {{-- Field yang benar-benar dikirim tetap `symptoms[]`,
                             supaya validasi & service di belakang tidak berubah. --}}
                        <input type="checkbox"
                               name="symptoms[]"
                               value="{{ $question['id'] }}"
                               hidden
                               @checked($answered)>

                        <div class="answer-group">
                            <button type="button" class="answer-btn answer-yes {{ $answered ? 'active' : '' }}"
                                    data-answer="yes">Ya</button>
                            <button type="button" class="answer-btn answer-no"
                                    data-answer="no">Tidak</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="actions">
                @if ($previousStep)
                    <a href="{{ route('detection.consultation.show', $previousStep) }}" class="btn btn-secondary">← Kembali</a>
                @else
                    <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">Batal</a>
                @endif

                <button type="submit" class="btn btn-primary">
                    {{ $isLastStep ? 'Lihat Hasil Konsultasi' : 'Lanjut →' }}
                </button>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    .question-list { display: flex; flex-direction: column; gap: .6rem; margin-top: 1.25rem; }
    .question-item {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        border: 1px solid #e0e0e0; border-radius: 8px; padding: .7rem .9rem; flex-wrap: wrap;
    }
    .question-text { font-size: .95rem; line-height: 1.4; flex: 1; min-width: 200px; }
    .answer-group { display: flex; gap: .4rem; flex-shrink: 0; }
    .answer-btn {
        min-width: 62px; padding: .4rem .8rem; border: 1px solid #cfd8dc; border-radius: 999px;
        background: #fff; color: #455a64; font-size: .85rem; font-weight: 600;
        cursor: pointer; font-family: inherit; transition: background .15s, color .15s, border-color .15s;
    }
    .answer-btn:hover { border-color: #90caf9; }
    .answer-yes.active { background: #2e7d32; border-color: #2e7d32; color: #fff; }
    .answer-no.active { background: #c62828; border-color: #c62828; color: #fff; }
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-question]').forEach(function (item) {
            const checkbox = item.querySelector('input[name="symptoms[]"]');
            const yes = item.querySelector('.answer-yes');
            const no = item.querySelector('.answer-no');

            function setAnswer(value) {
                checkbox.checked = (value === 'yes');
                yes.classList.toggle('active', value === 'yes');
                no.classList.toggle('active', value === 'no');
            }

            yes.addEventListener('click', () => setAnswer('yes'));
            no.addEventListener('click', () => setAnswer('no'));
        });
    </script>
@endsection
