<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Sistem Pakar Penyakit Lambung</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #bbdefb);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
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
            margin-bottom: 2rem;
        }
        .header h1 { color: #1565c0; font-size: 1.5rem; }
        .logout-btn {
            background: #e53935;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
        }
        .logout-btn:hover { background: #c62828; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-4px); }
        .card h3 { color: #1565c0; margin-bottom: 0.5rem; }
        .card p { color: #666; font-size: 0.9rem; }
        .welcome { color: #424242; font-size: 1.1rem; margin-bottom: 1rem; }
        .welcome strong { color: #1565c0; }
        @media(max-width: 768px) {
            .header { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🩺 Sistem Pakar Penyakit Lambung</h1>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">Keluar</button>
            </form>
        </div>

        <div class="welcome">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong>!
            Role: <strong>{{ auth()->user()->role }}</strong>
        </div>

        <div class="grid">
            @php
                $role = auth()->user()->role;
            @endphp

            @if($role === 'patient' || $role === 'admin' || $role === 'doctor')
                <div class="card">
                    <h3>📋 Konsultasi</h3>
                    <p>Masukkan gejala yang Anda alami untuk mendapatkan diagnosa.</p>
                </div>
            @endif

            @if($role === 'doctor' || $role === 'admin')
                <div class="card">
                    <h3>📊 Riwayat Diagnosa</h3>
                    <p>Lihat riwayat diagnosa pasien.</p>
                </div>
            @endif

            @if($role === 'editor' || $role === 'admin')
                <div class="card">
                    <h3>⚙️ Kelola Aturan</h3>
                    <p>Kelola aturan penalaran dan gejala penyakit.</p>
                </div>
                <div class="card">
                    <h3>📚 Kelola Penyakit</h3>
                    <p>Tambah, edit, dan hapus data penyakit.</p>
                </div>
            @endif

            @if($role === 'admin')
                <div class="card">
                    <h3>👥 Kelola Pengguna</h3>
                    <p>Kelola pengguna aplikasi.</p>
                </div>
            @endif

            @if($role === 'patient')
                <div class="card">
                    <h3>📄 Hasil Diagnosa</h3>
                    <p>Lihat hasil diagnosa sebelumnya.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
