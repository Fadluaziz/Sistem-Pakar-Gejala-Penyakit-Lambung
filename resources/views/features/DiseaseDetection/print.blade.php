<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Diagnosa - {{ $session->user->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; background: #eceff1; color: #222;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12.5px; line-height: 1.55;
        }
        .toolbar {
            display: flex; justify-content: space-between; align-items: center; gap: 1rem;
            max-width: 210mm; margin: 1rem auto; padding: .75rem 1rem; background: #fff;
            border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }
        .toolbar .hint { font-size: .85rem; color: #666; }
        .toolbar .actions { display: flex; gap: .5rem; }
        .toolbar a, .toolbar button {
            border: none; border-radius: 6px; padding: .55rem 1rem; font-size: .9rem;
            font-family: inherit; cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-print { background: #1565c0; color: #fff; }
        .btn-back { background: #eceff1; color: #37474f; }
        .sheet {
            width: 210mm; min-height: 297mm; margin: 0 auto 1.5rem; padding: 15mm;
            background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.12);
        }
        .letterhead { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; border-bottom: 3px solid #1565c0; padding-bottom: 10px; }
        .brand-name { font-size: 20px; font-weight: 700; color: #0d47a1; letter-spacing: .02em; }
        .brand-sub { font-size: 11px; color: #666; }
        .doc-meta { text-align: right; font-size: 11px; color: #555; }
        .doc-title { text-align: center; font-size: 15px; letter-spacing: .08em; margin: 18px 0 4px; color: #0d47a1; text-transform: uppercase; }
        .doc-subtitle { text-align: center; font-size: 11px; color: #666; margin-bottom: 14px; }
        .section { margin-top: 16px; }
        .section-title { font-size: 12px; font-weight: 700; color: #0d47a1; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #bbdefb; padding-bottom: 3px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        .kv th { width: 22%; text-align: left; font-weight: 600; color: #555; padding: 3px 6px 3px 0; vertical-align: top; font-size: 12px; }
        .kv td { width: 28%; padding: 3px 6px 3px 0; vertical-align: top; }
        .data th, .data td { border: 1px solid #cfd8dc; padding: 5px 7px; font-size: 11.5px; text-align: left; }
        .data th { background: #e3f2fd; color: #0d47a1; font-weight: 700; }
        .data td.num { text-align: center; }
        .data td.code { text-align: center; font-weight: 600; color: #1565c0; }
        .data td.right { text-align: right; }
        .data tr.total td { background: #f5f9ff; font-weight: 700; }
        .result-box { border: 2px solid #1565c0; border-radius: 6px; padding: 12px 14px; background: #f5f9ff; }
        .result-label { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: .05em; }
        .result-name { font-size: 18px; font-weight: 700; color: #0d47a1; margin: 2px 0; }
        .result-confidence { font-size: 12px; color: #333; }
        .bar { height: 8px; background: #cfd8dc; border-radius: 999px; overflow: hidden; margin: 6px 0 8px; }
        .bar-fill { height: 100%; background: #1565c0; }
        .muted { color: #666; }
        .warning { border: 1px solid #ef9a9a; background: #ffebee; color: #b71c1c; border-radius: 6px; padding: 8px 10px; margin-top: 10px; font-size: 11.5px; }
        .warning strong { display: block; margin-bottom: 2px; }
        .note-box { border: 1px solid #ffe082; background: #fffde7; border-radius: 6px; padding: 8px 10px; font-size: 11.5px; color: #6d4c41; }
        .disclaimer { border: 1px solid #ffb74d; background: #fff8e1; border-radius: 6px; padding: 10px 12px; font-size: 11px; color: #7c4a03; text-align: center; margin-top: 16px; }
        .signature { margin-top: 28px; display: flex; justify-content: space-between; gap: 1rem; }
        .signature .col { width: 45%; text-align: center; font-size: 11.5px; }
        .signature .space { height: 60px; }
        .signature .name { border-top: 1px solid #333; padding-top: 4px; font-weight: 600; }
        .footer { margin-top: 18px; border-top: 1px solid #cfd8dc; padding-top: 6px; font-size: 10px; color: #888; text-align: center; }
        .badge { display: inline-block; border-radius: 4px; padding: 1px 6px; font-size: 10.5px; font-weight: 700; }
        .badge-ok { background: #e8f5e9; color: #2e7d32; }
        .badge-bad { background: #ffebee; color: #c62828; }
        @page { size: A4; margin: 12mm; }
        @media print {
            body { background: #fff; font-size: 11.5px; }
            .toolbar { display: none !important; }
            .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            .section, .result-box, .signature { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    @php
        $primary = $diagnoses->firstWhere('rank', 1);
        $isDiagnosed = $session->status === \App\Features\DiseaseDetection\Models\ConsultationSession::STATUS_DIAGNOSED;
        $redFlags = $session->symptoms->where('category', \App\Features\DiseaseDetection\Models\Symptom::CATEGORY_BAHAYA);
        $patient = $session->user;
        $reportNo = 'LBC-' . str_pad((string) $session->id, 5, '0', STR_PAD_LEFT) . '/' . $session->created_at?->format('Y');
    @endphp

    <div class="toolbar">
        <span class="hint">Pada dialog cetak, pilih tujuan "Save as PDF" untuk menyimpan laporan ini.</span>
        <div class="actions">
            <a href="{{ route('detection.result', $session) }}" class="btn-back">Kembali ke Hasil</a>
            <button type="button" class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        </div>
    </div>

    <div class="sheet">
        <div class="letterhead">
            <div>
                <div class="brand-name">LambungCare</div>
                <div class="brand-sub">Sistem Pakar Gejala Penyakit Lambung</div>
            </div>
            <div class="doc-meta">
                <div>No. Laporan: {{ $reportNo }}</div>
                <div>Tanggal Konsultasi: {{ $session->created_at_label }}</div>
                <div>Dicetak: {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</div>
            </div>
        </div>

        <div class="doc-title">Laporan Hasil Diagnosa</div>
        <div class="doc-subtitle">Hasil konsultasi pasien pada sistem pakar forward chaining</div>

        <div class="section">
            <div class="section-title">A. Identitas Pasien</div>
            <table class="kv">
                <tr><th>Nama</th><td>{{ $patient->name }}</td><th>NIK</th><td>{{ $patient->nik ?: '-' }}</td></tr>
                <tr><th>Tanggal Lahir</th><td>{{ $patient->birth_date?->locale('id')->isoFormat('D MMMM YYYY') ?? '-' }}</td><th>Umur</th><td>{{ $patient->birth_date?->age !== null ? $patient->birth_date->age . ' tahun' : '-' }}</td></tr>
                <tr><th>Jenis Kelamin</th><td>{{ $patient->gender ?: '-' }}</td><th>Golongan Darah</th><td>{{ $patient->blood_type ?: '-' }}</td></tr>
                <tr><th>Telepon</th><td>{{ $patient->phone ?: '-' }}</td><th>Pekerjaan</th><td>{{ $patient->occupation ?: '-' }}</td></tr>
                <tr><th>Alamat</th><td colspan="3">{{ $patient->address ?: '-' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">B. Hasil Diagnosa</div>
            @if ($isDiagnosed && $primary)
                <div class="result-box">
                    <div class="result-label">Kemungkinan utama</div>
                    <div class="result-name">{{ $primary->disease->name }}</div>
                    <div class="result-confidence">Tingkat keyakinan {{ number_format($primary->confidence, 2) }}% (bobot {{ $primary->matchedWeight() }} dari {{ $primary->total_weight }})</div>
                    <div class="bar"><div class="bar-fill" style="width: {{ min(100, $primary->confidence) }}%"></div></div>
                    <div>{{ $primary->disease->description }}</div>
                </div>
            @else
                <div class="note-box">
                    <strong>Belum dapat disimpulkan.</strong>
                    @if ($session->status === \App\Features\DiseaseDetection\Models\ConsultationSession::STATUS_INSUFFICIENT)
                        Pasien hanya memilih {{ $session->symptoms->count() }} gejala, sedangkan sistem membutuhkan minimal
                        {{ \App\Features\DiseaseDetection\Services\InferenceService::MIN_SYMPTOMS }} gejala.
                    @else
                        Kombinasi gejala yang dipilih belum cukup khas untuk salah satu penyakit pada basis aturan.
                    @endif
                </div>
            @endif

            @if ($session->has_red_flag)
                <div class="warning">
                    <strong>Tanda bahaya terdeteksi</strong>
                    {{ $redFlags->pluck('name')->implode(', ') }}. Segera periksa ke dokter atau fasilitas kesehatan terdekat.
                </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">C. Gejala yang Dilaporkan</div>
            @if ($session->symptoms->isEmpty())
                <div class="muted">Tidak ada gejala yang dilaporkan.</div>
            @else
                <table class="data">
                    <thead>
                        <tr><th style="width: 8%;">No</th><th style="width: 12%;">Kode</th><th>Gejala</th><th style="width: 20%;">Kategori</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($session->symptoms as $index => $symptom)
                            <tr>
                                <td class="num">{{ $index + 1 }}</td>
                                <td class="code">{{ $symptom->code }}</td>
                                <td>{{ $symptom->name }}</td>
                                <td>{{ $symptom->category }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if ($isDiagnosed && $primary)
            <div class="section">
                <div class="section-title">D. Dasar Penalaran (Forward Chaining)</div>
                @if (empty($primary->breakdown))
                    <div class="muted">Tidak ada gejala pasien yang menjadi ciri khusus penyakit ini.</div>
                @else
                    <table class="data">
                        <thead>
                            <tr><th style="width: 8%;">No</th><th style="width: 12%;">Kode</th><th>Gejala</th><th style="width: 14%;">Bobot</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($primary->breakdown as $index => $reason)
                                <tr>
                                    <td class="num">{{ $index + 1 }}</td>
                                    <td class="code">{{ $reason['code'] }}</td>
                                    <td>{{ $reason['name'] }}</td>
                                    <td class="num">{{ $reason['weight'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="total">
                                <td colspan="3">Total bobot terkumpul / total bobot penyakit</td>
                                <td class="num">{{ $primary->matchedWeight() }} / {{ $primary->total_weight }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="muted" style="margin-top: 6px;">
                        Keyakinan = {{ $primary->matchedWeight() }} / {{ $primary->total_weight }} x 100% = {{ number_format($primary->confidence, 2) }}%.
                    </div>
                @endif
            </div>
        @endif

        @if ($diagnoses->count() > 1)
            <div class="section">
                <div class="section-title">E. Kemungkinan Lain</div>
                <table class="data">
                    <thead>
                        <tr><th style="width: 10%;">Rank</th><th>Penyakit</th><th style="width: 20%;">Keyakinan</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($diagnoses->where('rank', '>', 1) as $diagnosis)
                            <tr>
                                <td class="num">{{ $diagnosis->rank }}</td>
                                <td>{{ $diagnosis->disease->name }}</td>
                                <td class="right">{{ number_format($diagnosis->confidence, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($isDiagnosed && $primary && ($primary->disease->treatment || $primary->disease->prevention))
            <div class="section">
                <div class="section-title">F. Pengobatan dan Pencegahan</div>
                @if ($primary->disease->treatment)
                    <p><strong>Pengobatan:</strong> {{ $primary->disease->treatment }}</p>
                @endif
                @if ($primary->disease->prevention)
                    <p><strong>Pencegahan:</strong> {{ $primary->disease->prevention }}</p>
                @endif
            </div>
        @endif

        <div class="section">
            <div class="section-title">G. Catatan Dokter</div>
            @if ($session->isValidated())
                <p>
                    Status peninjauan:
                    @if ($session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_CORRECT)
                        <span class="badge badge-ok">Sesuai penilaian dokter</span>
                    @elseif ($session->validation === \App\Features\DiseaseDetection\Models\ConsultationSession::VALIDATION_INCORRECT)
                        <span class="badge badge-bad">Dinilai kurang tepat oleh dokter</span>
                    @endif
                </p>
                <p class="muted">Ditinjau oleh {{ $session->doctor?->name ?? 'Dokter' }} pada {{ $session->validated_at?->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}.</p>
                @if ($session->doctor_note)
                    <div class="note-box">{{ $session->doctor_note }}</div>
                @endif
            @else
                <p class="muted">Hasil ini belum ditinjau oleh dokter.</p>
            @endif
        </div>

        <div class="disclaimer">
            Diagnosa pada laporan ini dihasilkan mesin inferensi berbasis aturan dari gejala yang dipilih pasien dan bukan diagnosis medis.
            Hasil bersifat sementara; bila keluhan berlanjut, segera kunjungi dokter atau fasilitas kesehatan terdekat.
        </div>

        <div class="signature">
            <div class="col">
                <div>Pasien</div>
                <div class="space"></div>
                <div class="name">{{ $patient->name }}</div>
            </div>
            <div class="col">
                <div>Dokter Pemeriksa</div>
                <div class="space"></div>
                <div class="name">{{ $session->doctor?->name ?? '................................' }}</div>
            </div>
        </div>

        <div class="footer">
            Laporan dicetak dari LambungCare pada {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}. Nomor laporan {{ $reportNo }}.
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>
</body>
</html>
