@extends('layouts.app')

@section('title', 'Mengenal Penyakit Lambung')

@section('content')
    <div class="card">
        <h2>Mengenal Penyakit Lambung</h2>
        <div class="edu-intro muted" style="margin-top: .75rem;">
            <p>
                Lambung adalah organ berbentuk kantong yang berfungsi menyimpan dan mencerna
                makanan sementara, sekaligus membunuh kuman dengan asam yang dihasilkannya.
                Agar dinding lambung tidak ikut tercerna, lapisannya dilindungi oleh lendir
                khusus. Keluhan pada lambung umumnya muncul ketika lapisan pelindung ini
                terganggu, ketika asam lambung diproduksi berlebihan, atau ketika asam naik
                kembali ke kerongkongan.
            </p>
            <p>
                Keluhan yang terasa mirip belum tentu berasal dari penyakit yang sama, sehingga
                penting untuk mengenalinya lebih dulu. Kenali {{ $diseases->count() }} jenis
                penyakit lambung yang paling umum di bawah ini sebelum Anda memulai konsultasi.
            </p>
        </div>

        <div class="anchor-list">
            @foreach ($diseases as $disease)
                <a href="#{{ $disease->code }}">{{ $disease->name }}</a>
            @endforeach
        </div>
    </div>

    @foreach ($diseases as $disease)
        <div class="card" id="{{ $disease->code }}">
            <div class="edu-name">{{ $disease->name }}</div>
            <p class="muted" style="margin-top: .25rem;">{{ $disease->summary }}</p>

            <div class="edu-block">
                <div class="section-heading">🩺 Apa itu?</div>
                <p class="muted" style="line-height: 1.7;">{{ $disease->description }}</p>
            </div>

            <div class="edu-block">
                <div class="section-heading">🔍 Gejala Khas</div>
                @php
                    $keySymptoms = $disease->symptoms->sortByDesc('pivot.weight')->take(5);
                @endphp
                <div class="chip-list" style="margin-top: .5rem;">
                    @foreach ($keySymptoms as $symptom)
                        <span class="chip">
                            <span class="code-badge">{{ $symptom->code }}</span>{{ $symptom->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="edu-block">
                <div class="section-heading">💊 Pengobatan</div>
                <p class="muted" style="line-height: 1.7;">{{ $disease->treatment }}</p>
            </div>

            <div class="edu-block">
                <div class="section-heading">🛡️ Pencegahan</div>
                <p class="muted" style="line-height: 1.7;">{{ $disease->prevention }}</p>
            </div>
        </div>
    @endforeach

    <div class="card disclaimer">
        <strong>Catatan:</strong> penjelasan di halaman ini bersifat edukatif dan umum, bukan
        diagnosis. Keluhan yang mirip dapat berasal dari penyakit yang berbeda, dan hanya dokter
        yang dapat memastikan penyebabnya melalui pemeriksaan.
    </div>

    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">← Kembali ke Dashboard</a>
        <a href="{{ route('detection.consultation') }}" class="btn btn-primary">Mulai Konsultasi</a>
    </div>
@endsection
