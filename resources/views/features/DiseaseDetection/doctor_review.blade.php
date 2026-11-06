@extends('layouts.app')

@section('title', 'Riwayat Diagnosa Pasien')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Riwayat Diagnosa Pasien</h2>
                <p class="muted" style="margin-top: .25rem;">
                    Tinjau hasil diagnosa sistem, lalu beri tanda benar atau salah beserta
                    catatan medis Anda. Penilaian ini menjadi umpan balik untuk mengukur akurasi.
                </p>
            </div>
            <a href="{{ route('doctor.review.create') }}" class="btn btn-primary">+ Tambah Diagnosa Manual</a>
        </div>
    </div>

    <div class="stat-row">
        <div class="stat-box"><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total konsultasi</div></div>
        <div class="stat-box"><div class="stat-value">{{ $stats['validated'] }}</div><div class="stat-label">Sudah divalidasi</div></div>
        <div class="stat-box"><div class="stat-value">{{ $stats['correct'] }}</div><div class="stat-label">Valid benar</div></div>
        <div class="stat-box"><div class="stat-value">{{ $stats['incorrect'] }}</div><div class="stat-label">Valid salah</div></div>
    </div>

    <div class="card">
        <div class="review-tabs">
            <a href="{{ route('doctor.review.index') }}" class="tab {{ $filter === 'all' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('doctor.review.filter', 'unvalidated') }}" class="tab {{ $filter === 'unvalidated' ? 'active' : '' }}">Belum ditinjau</a>
            <a href="{{ route('doctor.review.filter', 'validated') }}" class="tab {{ $filter === 'validated' ? 'active' : '' }}">Sudah ditinjau</a>
        </div>

        @forelse ($sessions as $session)
            @php $top = $session->diagnoses->firstWhere('rank', 1); @endphp
            <div class="review-item">
                <div class="history-head" style="gap: 1rem;">
                    <div class="review-meta">
                        <div class="review-patient">{{ $session->user?->name ?? 'Pengguna tidak dikenal' }}</div>
                        <div class="muted">{{ $session->created_at_label }}
                            &nbsp;·&nbsp; {{ $session->symptoms_count }} gejala</div>
                    </div>
                    <div class="history-badges">
                        @if ($session->isValidated())
                            <span class="pill {{ $session->validation === 'correct' ? 'pill-success' : 'pill-danger' }}">
                                {{ $session->validation === 'correct' ? 'Benar' : 'Salah' }}
                            </span>
                        @else
                            <span class="pill">Belum ditinjau</span>
                        @endif
                    </div>
                </div>

                <div class="review-diag">
                    @if ($session->status === 'diagnosed' && $top)
                        <strong>{{ $top->disease->name }}</strong>
                        <span class="muted">— {{ number_format($top->confidence, 0) }}%</span>
                    @else
                        <span class="muted">Belum dapat disimpulkan</span>
                    @endif
                </div>

                <div class="actions" style="margin-top: 1.25rem;">
                    <span class="muted"></span>
                    <a href="{{ route('doctor.review.show', $session->id) }}" class="btn btn-secondary">Tinjau</a>
                </div>
            </div>
        @empty
            <p class="muted" style="padding: 1rem 0;">Belum ada hasil diagnosa untuk ditinjau.</p>
        @endforelse
    </div>
@endsection

@section('styles')
    .review-tabs { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
    .review-tabs .tab {
        padding: .45rem 1rem; border-radius: 999px; font-size: .85rem; text-decoration: none;
        background: #eceff1; color: #37474f;
    }
    .review-tabs .tab.active { background: #1565c0; color: #fff; }
    .review-tabs .tab:hover { background: #cfd8dc; }
    .review-tabs .tab.active:hover { background: #0d47a1; }
    .review-item {
        border-top: 1px solid #f0f0f0; padding: 1.1rem 0;
    }
    .review-item:first-of-type { border-top: none; padding-top: 0; }
    .review-patient { font-weight: 700; color: #333; }
    .review-diag { margin-top: .5rem; }
@endsection