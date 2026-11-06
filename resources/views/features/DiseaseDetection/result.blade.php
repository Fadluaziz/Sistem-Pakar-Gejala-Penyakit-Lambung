@extends('layouts.app')

@section('title', 'Hasil Konsultasi')

@section('styles')
    <style>
        /* ===== Tampilan cetak / simpan PDF ===== */
        .print-only { display: none; }
        .print-header { text-align: center; margin-bottom: 1.5rem; border-bottom: 2px solid #1565c0; padding-bottom: 1rem; }
        .print-header h1 { color: #1565c0; font-size: 1.4rem; margin-bottom: .25rem; }
        .print-header p { color: #666; font-size: .85rem; }
        .print-meta { width: 100%; border-collapse: collapse; margin-top: .75rem; font-size: .9rem; }
        .print-meta th, .print-meta td { text-align: left; padding: .4rem .6rem; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        .print-meta th { width: 32%; color: #555; font-weight: 600; }
        .print-signature { margin-top: 2.5rem; justify-content: space-between; font-size: .9rem; }
        .print-signature div { text-align: center; width: 40%; }
        .print-signature .line { margin-top: 4rem; border-top: 1px solid #333; padding-top: .4rem; }

        @media print {
            body { background: #fff !important; }
            .container { max-width: 100%; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .print-signature.print-only { display: flex !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd; break-inside: avoid; }
            .card-primary { background: #fff !important; color: #000 !important; border: 2px solid #1565c0 !important; }
            .card-primary h2, .card-primary .muted, .card-primary .confidence-value { color: #000 !important; }
            .confidence-track.on-primary { background: #eceff1 !important; }
            .confidence-track.on-primary .confidence-fill { background: #1565c0 !important; }
            .final-disclaimer, .disclaimer { background: #fff !important; border-color: #999 !important; color: #000 !important; }
            a { color: #000 !important; text-decoration: none !important; }
        }
    </style>
@endsection

@section('content')
    @php
        $primary = $diagnoses->firstWhere('rank', 1);
        $isDiagnosed = $session->status === \App\Features\DiseaseDetection\Models\ConsultationSession::STATUS_DIAGNOSED;
        $redFlags = $session->symptoms->where('category', \App\Features\DiseaseDetection\Models\Symptom::CATEGORY_BAHAYA);
        $patient = $session->user;
    @endphp

    <div class="print-only print-header">
        <h1>LambungCare - Hasil Diagnosa Penyakit Lambung</h1>
        <p>Laporan hasil konsultasi sistem pakar forward chaining</p>
    </div>

    <div class="card no-print">
        <div class="history-head">
            <div>
                <h2>Hasil Konsultasi</h2>
            </div>
            <a href="{{ route('detection.result.print', $session) }}" target="_blank" class="btn btn-primary">Cetak / Simpan PDF</a>
            <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>
    </div>

    {{-- Data diri pasien (tampil di layar & cetak) --}}
    <div class="card">
        <h2>Data Pasien</h2>
        <table class="print-meta">
            <tr><th>Nama</th><td>{{ $patient->name }}</td></tr>
            <tr><th>NIK</th><td>{{ $patient->nik ?: '-' }}</td></tr>
            <tr><th>Tanggal Lahir</th><td>{{ $patient->birth_date?->locale('id')->isoFormat('D MMMM YYYY') ?? '-' }}</td></tr>
            <tr><th>Jenis Kelamin</th><td>{{ $patient->gender ?: '-' }}</td></tr>
            <tr><th>Golongan Darah</th><td>{{ $patient->blood_type ?: '-' }}</td></tr>
            <tr><th>Pekerjaan</th><td>{{ $patient->occupation ?: '-' }}</td></tr>
            <tr><th>Telepon</th><td>{{ $patient->phone ?: '-' }}</td></tr>
            <tr><th>Alamat</th><td>{{ $patient->address ?: '-' }}</td></tr>
            <tr><th>Tanggal Konsultasi</th><td>{{ $session->created_at_label }}</td></tr>
        </table>
        @if (! $patient->nik)
            <p class="muted no-print" style="margin-top: .75rem;">
                Lengkapi <a href="{{ route('profile.edit') }}">profil Anda</a> agar data diri tampil lengkap pada hasil cetak.
            </p>
        @endif
    </div>

    @if ($session->has_red_flag)
        <div class="alert alert-error">
            <strong>⚠️ Perhatian — tanda bahaya terdeteksi</strong>
            <ul>
                @foreach ($redFlags as $flag)
                    <li>{{ $flag->name }}</li>
                @endforeach
            </ul>
            <p style="margin-top: .5rem;">
                Keluhan tersebut dapat menandakan perdarahan pada saluran cerna.
                Segera periksa ke dokter atau fasilitas kesehatan terdekat, jangan menunggu.
            </p>
        </div>
    @endif

    @if ($isDiagnosed && $primary)
        <div class="card card-primary">
            <p class="muted">Kemungkinan utama</p>
            <h2 style="font-size: 1.4rem;">{{ $primary->disease->name }}</h2>

            <div class="confidence-row">
                <span class="confidence-value" style="color: #fff;">{{ number_format($primary->confidence, 0) }}%</span>
                <span class="muted">tingkat keyakinan</span>
            </div>
            <div class="confidence-track on-primary">
                <div class="confidence-fill" style="width: {{ min(100, $primary->confidence) }}%"></div>
            </div>

            <p style="margin-top: 1.25rem;">{{ $primary->disease->description }}</p>
        </div>
    @else
        <div class="card">
            <h2>Belum dapat disimpulkan</h2>
            <p class="muted" style="margin-top: .5rem;">
                @if ($session->status === \App\Features\DiseaseDetection\Models\ConsultationSession::STATUS_INSUFFICIENT)
                    Anda hanya memilih {{ $session->symptoms->count() }} gejala. Pilih minimal
                    {{ \App\Features\DiseaseDetection\Services\InferenceService::MIN_SYMPTOMS }} gejala
                    agar sistem dapat membandingkan kemungkinan penyakit.
                @else
                    Kombinasi gejala yang Anda pilih belum cukup khas untuk salah satu penyakit.
                    Coba periksa kembali gejala yang Anda rasakan, atau konsultasikan langsung ke dokter.
                @endif
            </p>
        </div>
    @endif

    {{-- Penjelasan: dari jawaban awal sampai kesimpulan akhir --}}
    <div class="card">
        <h2>Bagaimana Kesimpulan Ini Diperoleh</h2>
        <p class="muted" style="margin-top: .35rem;">
            Sistem menelusuri {{ $session->symptoms->count() }} gejala yang Anda pilih
            (<em>forward chaining</em>). Setiap gejala yang menjadi ciri suatu penyakit
            menambah bobot penilaiannya. Penyakit dengan bobot terkumpul terbanyak menjadi
            kesimpulan.
        </p>

        @if ($primary)
            <div class="edu-block">
                <div class="section-heading" style="margin-top: 1.25rem;">
                    Gejala Anda yang mendukung {{ $primary->disease->name }}
                </div>

                @if (empty($primary->breakdown))
                    <p class="muted">Tidak ada gejala yang secara khusus menjadi ciri penyakit ini.</p>
                @else
                    <div class="reason-list">
                        @foreach ($primary->breakdown as $reason)
                            <div class="reason-row">
                                <span>
                                    <span class="code-badge">{{ $reason['code'] }}</span>{{ $reason['name'] }}
                                </span>
                                <span class="weight-badge">bobot {{ $reason['weight'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="reason-total">
                        Bobot terkumpul dari gejala Anda: <strong>{{ $primary->matchedWeight() }}</strong>
                        &nbsp;·&nbsp;
                        Keyakinan = {{ $primary->matchedWeight() }} ÷ {{ $primary->total_weight }}
                        &nbsp;→&nbsp;
                        <strong>{{ number_format($primary->confidence, 2) }}%</strong>
                        dari seluruh ciri penyakit ini.
                    </div>
                @endif
            </div>
        @endif

        @if ($session->symptoms->isNotEmpty())
            <div class="edu-block">
                <div class="section-heading">Seluruh gejala yang Anda pilih</div>
                <div class="chip-list">
                    @foreach ($session->symptoms as $symptom)
                        <span class="chip">
                            <span class="code-badge">{{ $symptom->code }}</span>{{ $symptom->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($diagnoses->count() > 1)
        <div class="card">
            <h2>Kemungkinan Lain</h2>
            <p class="muted" style="margin-bottom: .75rem;">
                Diurutkan dari yang paling mendekati. Bisa jadi Dokter menilai sebaliknya.
            </p>

            @foreach ($diagnoses->where('rank', '>', 1) as $diagnosis)
                <div class="candidate">
                    <div class="candidate-head">
                        <span class="candidate-name">{{ $diagnosis->disease->name }}</span>
                        <span class="muted">{{ number_format($diagnosis->confidence, 0) }}%</span>
                    </div>
                    <div class="confidence-track">
                        <div class="confidence-fill" style="width: {{ min(100, $diagnosis->confidence) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pengobatan & pencegahan hanya untuk kesimpulan yang benar-benar terdiagnosis;
         pada hasil "belum dapat disimpulkan" menampilkan ini akan menyesatkan. --}}
    @if ($isDiagnosed && $primary)
        @if ($primary->disease->treatment)
            <div class="card">
                <div class="section-heading" style="font-size: 1.15rem;">💊 Cara Pengobatan</div>
                <p class="muted" style="line-height: 1.75;">{{ $primary->disease->treatment }}</p>
            </div>
        @endif

        @if ($primary->disease->prevention)
            <div class="card">
                <div class="section-heading" style="font-size: 1.15rem;">🛡️ Cara Pencegahan</div>
                <p class="muted" style="line-height: 1.75;">{{ $primary->disease->prevention }}</p>
            </div>
        @endif
    @endif

    <div class="final-disclaimer">
        Disclaimer: diagnosa pada website ini bersifat sementara, apabila terjadi keluhan
        lanjutan silahkan kunjungi dokter terdekat untuk mendapatkan pengobatan lebih lanjut!
    </div>

    <div class="card disclaimer">
        <strong>Catatan:</strong> hasil ini dihasilkan mesin inferensi berbasis aturan
        (<em>forward chaining</em>) dari gejala yang Anda pilih sendiri, dan <strong>bukan
        diagnosis medis</strong>. Akurasinya bergantung pada kelengkapan jawaban Anda serta
        basis aturan yang tersedia.
    </div>

    <div class="print-only print-signature">
        <div>
            <div>Pasien</div>
            <div class="line">{{ $patient->name }}</div>
        </div>
        <div>
            <div>Dokter Pemeriksa</div>
            <div class="line">&nbsp;</div>
        </div>
    </div>

    <div class="actions no-print" style="margin-bottom: 2rem;">
        <a href="{{ route('detection.history') }}" class="btn btn-secondary">Riwayat Konsultasi</a>
        <a href="{{ route('detection.consultation') }}" class="btn btn-primary">Konsultasi Ulang</a>
    </div>
@endsection
