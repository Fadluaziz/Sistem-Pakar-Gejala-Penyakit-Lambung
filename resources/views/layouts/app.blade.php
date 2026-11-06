<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LambungCare')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #bbdefb);
            min-height: 100vh;
            color: #333;
        }
        .container { max-width: 860px; margin: 2rem auto; padding: 0 1rem; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .header h1 { color: #1565c0; font-size: 1.3rem; }
        .header-user { display: flex; align-items: center; gap: 1rem; font-size: .9rem; color: #666; }
        .logout-btn {
            background: #e53935; color: #fff; border: none;
            padding: .5rem 1rem; border-radius: 6px; cursor: pointer; font-size: .9rem;
        }
        .logout-btn:hover { background: #c62828; }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .card h2 { color: #1565c0; font-size: 1.15rem; margin-bottom: .5rem; }
        .card h3 { color: #1565c0; font-size: 1rem; margin-bottom: .5rem; }
        .muted { color: #666; font-size: .9rem; }

        .alert { padding: .85rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .9rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert ul { margin: .5rem 0 0 1.1rem; }

        .btn {
            display: inline-block; padding: .7rem 1.4rem; border-radius: 8px; border: none;
            font-size: .95rem; cursor: pointer; text-decoration: none; text-align: center;
            font-family: inherit;
        }
        .btn-primary { background: #1565c0; color: #fff; }
        .btn-primary:hover { background: #0d47a1; }
        .btn-secondary { background: #eceff1; color: #37474f; }
        .btn-secondary:hover { background: #cfd8dc; }
        .btn.disabled { opacity: .45; cursor: not-allowed; }

        .actions { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 1.5rem; }

        /* Form */
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; margin-bottom: .4rem; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: .6rem .75rem; border: 1px solid #ccc; border-radius: 8px;
            font-size: .95rem; font-family: inherit; background: #fff;
        }
        .form-group textarea { min-height: 90px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Tabel keterangan (profil & hasil) */
        .print-meta { width: 100%; border-collapse: collapse; margin-top: .75rem; font-size: .9rem; }
        .print-meta th, .print-meta td { text-align: left; padding: .45rem .6rem; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        .print-meta th { width: 32%; color: #555; font-weight: 600; }
        @media(max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        /* Progres langkah wizard */
        .progress-label { font-size: .85rem; color: #1565c0; font-weight: 600; margin-bottom: .4rem; }
        .progress-track { background: #eceff1; border-radius: 999px; height: 8px; overflow: hidden; }
        .progress-fill { background: #1565c0; height: 100%; transition: width .3s; }

        /* Daftar gejala (checkbox) */
        .symptom-list { display: flex; flex-direction: column; gap: .6rem; margin-top: 1.25rem; }
        .symptom-item {
            display: flex; align-items: flex-start; gap: .7rem;
            border: 1px solid #e0e0e0; border-radius: 8px; padding: .75rem .9rem;
            cursor: pointer; font-size: .95rem; line-height: 1.4;
        }
        .symptom-item:hover { border-color: #90caf9; background: #f5faff; }
        .symptom-item input { margin-top: .2rem; width: 18px; height: 18px; accent-color: #1565c0; flex-shrink: 0; }
        .code-badge {
            display: inline-block; background: #e3f2fd; color: #1565c0;
            font-size: .72rem; font-weight: 700; border-radius: 4px;
            padding: .1rem .35rem; margin-right: .4rem; vertical-align: middle;
        }

        /* Hasil & tingkat keyakinan */
        .confidence-row { display: flex; align-items: baseline; gap: .5rem; margin: .5rem 0 .4rem; }
        .confidence-value { font-size: 1.8rem; font-weight: 700; color: #1565c0; }
        .confidence-track { background: #eceff1; border-radius: 999px; height: 10px; overflow: hidden; }
        .confidence-fill { background: linear-gradient(90deg, #42a5f5, #1565c0); height: 100%; }
        /* Latar terang agar bar tetap terbaca di dalam kartu hasil yang berwarna. */
        .confidence-track.on-primary { background: #ffffff59; }
        .confidence-track.on-primary .confidence-fill { background: #fff; }

        .candidate { padding: .75rem 0; border-bottom: 1px solid #f0f0f0; }
        .candidate:last-child { border-bottom: none; }
        .candidate-head { display: flex; justify-content: space-between; font-size: .92rem; margin-bottom: .35rem; }
        .candidate-name { font-weight: 600; }

        .chip-list { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; }
        .chip {
            background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 999px;
            padding: .3rem .7rem; font-size: .82rem;
        }

        /* Riwayat */
        .history-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
        .history-date { font-size: .82rem; color: #888; }
        .history-result { font-size: 1rem; font-weight: 600; color: #1565c0; margin-top: .2rem; }
        .history-badges { display: flex; gap: .4rem; flex-wrap: wrap; justify-content: flex-end; }
        .pill {
            background: #eceff1; color: #455a64; font-size: .75rem;
            border-radius: 999px; padding: .2rem .6rem; white-space: nowrap;
        }
        .pill-danger { background: #ffebee; color: #c62828; }
        .pill-success { background: #e8f5e9; color: #2e7d32; }

        /* Peninjauan dokter */
        .stat-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .stat-box { flex: 1; min-width: 130px; background: #fff; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #1565c0; }
        .stat-label { font-size: .82rem; color: #666; }
        .review-label { font-size: .82rem; font-weight: 700; color: #455a64; margin-bottom: .5rem; }
        .review-choice { display: flex; gap: .5rem; margin-bottom: 1rem; }
        .review-choice label {
            flex: 1; border: 1px solid #bdbdbd; border-radius: 8px; padding: .65rem .9rem;
            font-size: .92rem; cursor: pointer; text-align: center; transition: background .15s, color .15s;
        }
        .review-choice input { display: none; }
        .review-choice input[id="validation-correct"]:checked ~ label[for="validation-correct"] { background: #2e7d32; border-color: #2e7d32; }
        .review-choice input[id="validation-incorrect"]:checked ~ label[for="validation-incorrect"] { background: #c62828; border-color: #c62828; }
        .review-choice input[id="validation-correct"]:checked ~ label[for="validation-correct"],
        .review-choice input[id="validation-incorrect"]:checked ~ label[for="validation-incorrect"] { color: #fff; font-weight: 700; }
        .review-ta textarea { width: 100%; min-height: 90px; padding: .65rem .75rem; border: 1px solid #ccc; border-radius: 8px; font-size: .95rem; font-family: inherit; }
        .doctor-note { margin-top: .9rem; padding: .75rem 1rem; background: #f5f5f5; border-radius: 8px; font-size: .9rem; }
        .doctor-note-label { font-size: .78rem; font-weight: 700; color: #888; }

        .pagination { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
        .pagination .muted { font-size: .85rem; }

        .disclaimer { background: #fff8e1; border: 1px solid #ffe082; font-size: .86rem; color: #6d4c41; }

        /* Halaman edukasi */
        .edu-intro { line-height: 1.75; }
        .edu-intro p + p { margin-top: .75rem; }
        .edu-block { margin-top: 1.25rem; }
        .edu-block:first-child { margin-top: 0; }
        .edu-name { color: #1565c0; font-size: 1.1rem; font-weight: 700; }
        .anchor-list { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; }
        .anchor-list a {
            background: #e3f2fd; color: #1565c0; text-decoration: none;
            border-radius: 999px; padding: .3rem .75rem; font-size: .85rem;
        }
        .anchor-list a:hover { background: #bbdefb; }

        /* Jejak penalaran pada halaman hasil */
        .reason-list { display: flex; flex-direction: column; gap: .4rem; margin-top: .75rem; }
        .reason-row {
            display: flex; justify-content: space-between; align-items: center; gap: 1rem;
            background: #f7fbff; border: 1px solid #e3f2fd;
            border-radius: 6px; padding: .55rem .75rem; font-size: .88rem;
        }
        .weight-badge {
            background: #1565c0; color: #fff; border-radius: 4px; font-weight: 700;
            font-size: .75rem; padding: .15rem .45rem; white-space: nowrap;
        }
        .reason-total {
            margin-top: .85rem; padding-top: .85rem; border-top: 1px dashed #cfd8dc;
            font-size: .88rem; color: #455a64;
        }

        .section-heading {
            color: #1565c0; font-size: 1rem; margin-bottom: .4rem;
            display: flex; align-items: center; gap: .4rem;
        }

        /* Disclaimer penutup hasil diagnosa */
        .final-disclaimer {
            background: #fff3e0; border: 2px solid #ffb74d; border-radius: 10px;
            padding: 1.1rem 1.25rem; margin-bottom: 1rem;
            font-size: .9rem; font-weight: 600; color: #7c4a03;
            line-height: 1.6; text-align: center;
        }

        /* Kartu kesimpulan utama — disorot agar menonjol di antara kartu lain */
        .card-primary {
            background: linear-gradient(135deg, #1565c0, #0d47a1);
            color: #fff;
        }
        .card-primary h2, .card-primary h3 { color: #fff; }
        .card-primary .muted { color: #ffffffc4; }

        @media(max-width: 768px) {
            .header { flex-direction: column; gap: 1rem; }
            .actions { flex-direction: column-reverse; }
            .actions .btn { width: 100%; }
            .history-head { flex-direction: column; }
        }
        @yield('styles')
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🩺 LambungCare</h1>
            <div class="header-user">
                <span>{{ auth()->user()?->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">Keluar</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
