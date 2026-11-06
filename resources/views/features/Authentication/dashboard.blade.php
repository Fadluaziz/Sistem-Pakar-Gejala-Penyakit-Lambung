<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – LambungCare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #bbdefb);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
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
        .header h1 { color: #1565c0; font-size: 1.4rem; }
        .logout-btn {
            background: #e53935;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-size: .9rem;
        }
        .logout-btn:hover { background: #c62828; }

        /* Hero sambutan */
        .hero {
            background: linear-gradient(135deg, #1565c0, #0d47a1);
            color: #fff;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 6px 18px rgba(21,101,192,0.25);
        }
        .hero .greeting { font-size: 1.5rem; font-weight: 700; }
        .hero .tagline { color: #ffffffd0; margin-top: .35rem; font-size: .95rem; }

        /* Kotak ringkasan angka */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-box {
            background: #fff;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-left: 4px solid #1565c0;
        }
        .stat-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #78909c; font-weight: 700; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #0d47a1; margin: .25rem 0 .15rem; line-height: 1.2; }
        .stat-hint { font-size: .8rem; color: #888; }

        .section-title { color: #1565c0; font-size: 1.05rem; font-weight: 700; margin: 0 0 .85rem; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-4px); }
        .card-link { display: block; text-decoration: none; color: inherit; }
        .card h3 { color: #1565c0; margin-bottom: 0.5rem; font-size: 1.05rem; }
        .card p { color: #666; font-size: 0.9rem; line-height: 1.5; }

        /* Tips kesehatan */
        .tips { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .tips ul { list-style: none; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: .6rem .9rem; }
        .tips li { font-size: .9rem; color: #555; padding-left: 1.5rem; position: relative; line-height: 1.5; }
        .tips li::before { content: '✓'; position: absolute; left: 0; color: #2e7d32; font-weight: 700; }

        @media(max-width: 768px) {
            .header { flex-direction: column; gap: 1rem; }
            .hero { padding: 1.5rem; }
            .hero .greeting { font-size: 1.25rem; }
        }
    </style>
</head>
<body>
    @php $role = auth()->user()->role; @endphp

    <div class="container">
        <div class="header">
            <h1>🩺 LambungCare</h1>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">Keluar</button>
            </form>
        </div>

        {{-- Hero sambutan --}}
        <div class="hero">
            <div class="greeting">Selamat datang, {{ auth()->user()->name }}!</div>
            <div class="tagline">
                @if($role === 'patient')
                    Kenali gejala lambung Anda, lalu dapatkan gambaran awal sebelum ke dokter.
                @elseif($role === 'doctor' || $role === 'admin')
                    Tinjau hasil diagnosa pasien dan berikan penilaian medis Anda.
                @else
                    Kelola data penyakit, gejala, dan aturan penalaran sistem.
                @endif
            </div>
        </div>

        {{-- Ringkasan angka --}}
        <div class="stat-row">
            @foreach ($stats as $stat)
                <div class="stat-box">
                    <div class="stat-label">{{ $stat['label'] }}</div>
                    <div class="stat-value">{{ $stat['value'] }}</div>
                    <div class="stat-hint">{{ $stat['hint'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Menu --}}
        <p class="section-title">Menu</p>
        <div class="grid">
            @if($role === 'patient' || $role === 'admin' || $role === 'doctor')
                <a class="card card-link" href="{{ route('detection.diseases') }}">
                    <h3>📖 Mengenal Penyakit Lambung</h3>
                    <p>Pelajari jenis-jenis penyakit lambung sebelum berkonsultasi.</p>
                </a>
                <a class="card card-link" href="{{ route('detection.consultation') }}">
                    <h3>📋 Konsultasi</h3>
                    <p>Masukkan gejala yang Anda alami untuk mendapatkan diagnosa.</p>
                </a>
            @endif

            @if($role === 'doctor' || $role === 'admin')
                <a class="card card-link" href="{{ route('doctor.review.index') }}">
                    <h3>📊 Riwayat Diagnosa</h3>
                    <p>Tinjau & verifikasi hasil diagnosa pasien.</p>
                </a>
            @endif

            @if($role === 'editor' || $role === 'admin')
                <a class="card card-link" href="{{ route('editor.rules.index') }}">
                    <h3>⚙️ Kelola Aturan</h3>
                    <p>Kelola bobot aturan penalaran dan gejala penyakit.</p>
                </a>
                <a class="card card-link" href="{{ route('editor.diseases.index') }}">
                    <h3>📚 Kelola Penyakit</h3>
                    <p>Tambah, edit, dan hapus data penyakit.</p>
                </a>
            @endif

            @if($role === 'admin')
                <div class="card">
                    <h3>👥 Kelola Pengguna</h3>
                    <p>Kelola pengguna aplikasi.</p>
                </div>
            @endif

            @if($role === 'patient')
                <a class="card card-link" href="{{ route('detection.history') }}">
                    <h3>📄 Hasil Diagnosa</h3>
                    <p>Lihat hasil diagnosa sebelumnya.</p>
                </a>
                <a class="card card-link" href="{{ route('profile.edit') }}">
                    <h3>Profil Saya</h3>
                    <p>Lengkapi data diri untuk dicetak pada hasil diagnosa.</p>
                </a>
            @endif
        </div>

        {{-- Tips kesehatan --}}
        <div class="tips">
            <p class="section-title">💡 Tips Menjaga Kesehatan Lambung</p>
            <ul>
                <li>Makan teratur dan jangan melewatkan waktu makan.</li>
                <li>Kurangi makanan pedas, asam, dan berlemak berlebihan.</li>
                <li>Batasi kopi, minuman bersoda, dan alkohol.</li>
                <li>Jangan langsung berbaring dalam 2–3 jam setelah makan.</li>
                <li>Berhenti merokok dan kelola stres dengan cukup istirahat.</li>
                <li>Minum air putih yang cukup setiap hari.</li>
            </ul>
        </div>
    </div>
</body>
</html>
