@extends('layouts.app')

@section('title', 'Riwayat Konsultasi')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Riwayat Konsultasi</h2>
                <p class="muted" style="margin-top: .25rem;">Daftar konsultasi yang pernah Anda lakukan, terbaru lebih dulu.</p>
            </div>
            <a href="{{ route('patient.dashboard') }}" class="btn btn-primary">← Kembali ke Dashboard</a>
        </div>
    </div>

    @forelse ($sessions as $session)
        @php $top = $session->diagnoses->firstWhere('rank', 1); @endphp

        <div class="card">
            <div class="history-head">
                <div>
                    <div class="history-date">{{ $session->created_at_label }}</div>

                    @if ($session->status === \App\Features\DiseaseDetection\Models\ConsultationSession::STATUS_DIAGNOSED && $top)
                        <div class="history-result">
                            {{ $top->disease->name }} — {{ number_format($top->confidence, 0) }}%
                        </div>
                    @else
                        <div class="history-result muted">Belum dapat disimpulkan</div>
                    @endif
                </div>

                <div class="history-badges">
                    @if ($session->has_red_flag)
                        <span class="pill pill-danger">Tanda bahaya</span>
                    @endif
                    <span class="pill">{{ $session->symptoms_count }} gejala</span>
                </div>
            </div>

            <div class="actions">
                <span class="muted"></span>
                <a href="{{ route('detection.result', $session) }}" class="btn btn-secondary">Lihat Hasil</a>
            </div>
        </div>
    @empty
        <div class="card">
            <p class="muted">Anda belum pernah melakukan konsultasi.</p>
            <div class="actions">
                <span class="muted"></span>
                <a href="{{ route('detection.consultation') }}" class="btn btn-primary">Mulai Konsultasi</a>
            </div>
        </div>
    @endforelse

    @if ($sessions->hasPages())
        <div class="pagination">
            @if ($sessions->onFirstPage())
                <span class="btn btn-secondary disabled">← Sebelumnya</span>
            @else
                <a href="{{ $sessions->previousPageUrl() }}" class="btn btn-secondary">← Sebelumnya</a>
            @endif

            <span class="muted">Halaman {{ $sessions->currentPage() }} dari {{ $sessions->lastPage() }}</span>

            @if ($sessions->hasMorePages())
                <a href="{{ $sessions->nextPageUrl() }}" class="btn btn-secondary">Berikutnya →</a>
            @else
                <span class="btn btn-secondary disabled">Berikutnya →</span>
            @endif
        </div>
    @endif
@endsection
