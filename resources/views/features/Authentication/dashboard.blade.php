<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LambungCare</title>
    <style>
        :root {
            --blue-900: #0d47a1;
            --blue-700: #1565c0;
            --blue-100: #e3f2fd;
            --ink: #24303f;
            --ink-soft: #5b6b7c;
            --line: #e4eaf1;
            --surface: #ffffff;
            --radius: 14px;
            --shadow: 0 6px 20px rgba(21, 101, 192, 0.08);
            --shadow-lg: 0 12px 32px rgba(21, 101, 192, 0.16);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f2f6fb;
            min-height: 100vh;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3rem;
        }

        /* ---------- Header ---------- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            background: var(--surface);
            padding: .9rem 1.4rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        .brand { display: flex; align-items: center; gap: .7rem; min-width: 0; }
        .brand-mark {
            width: 38px; height: 38px; flex-shrink: 0;
            display: grid; place-items: center;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--blue-700), var(--blue-900));
            box-shadow: 0 4px 12px rgba(21, 101, 192, .3);
        }
        .brand-mark svg { width: 21px; height: 21px; stroke: #fff; }
        .brand-text { min-width: 0; }
        .brand-text h1 {
            font-size: 1.15rem; font-weight: 700; color: var(--blue-900);
            line-height: 1.2; letter-spacing: -.01em;
        }
        .brand-text span { font-size: .78rem; color: var(--ink-soft); }

        .header-right { display: flex; align-items: center; gap: .9rem; flex-shrink: 0; }
        .user-chip {
            display: flex; align-items: center; gap: .6rem;
            padding: .35rem .75rem .35rem .4rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fafcff;
            max-width: 260px;
        }
        .avatar {
            width: 30px; height: 30px; flex-shrink: 0;
            display: grid; place-items: center;
            border-radius: 50%;
            background: var(--blue-100);
            color: var(--blue-700);
            font-weight: 700; font-size: .82rem;
            text-transform: uppercase;
        }
        .user-chip .name {
            font-size: .85rem; font-weight: 600; color: var(--ink);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .logout-btn {
            background: #fff; color: #c62828;
            border: 1px solid #f3c6c6;
            padding: .45rem .9rem; border-radius: 8px;
            cursor: pointer; font-family: inherit; font-size: .85rem; font-weight: 600;
            transition: background .15s, border-color .15s;
        }
        .logout-btn:hover { background: #ffebee; border-color: #e57373; }

        /* ---------- Hero ---------- */
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--blue-700), var(--blue-900));
            color: #fff;
            border-radius: var(--radius);
            padding: 1.9rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
        }
        .hero::after {
            content: '';
            position: absolute; right: -70px; top: -90px;
            width: 260px; height: 260px; border-radius: 50%;
            background: rgba(255, 255, 255, .09);
        }
        .hero::before {
            content: '';
            position: absolute; right: 60px; bottom: -120px;
            width: 190px; height: 190px; border-radius: 50%;
            background: rgba(255, 255, 255, .06);
        }
        .hero-content { position: relative; z-index: 1; max-width: 620px; }
        .hero .eyebrow {
            display: inline-block;
            font-size: .72rem; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: #bbdefb;
            margin-bottom: .55rem;
        }
        .hero .greeting {
            font-size: 1.55rem; font-weight: 700; line-height: 1.25;
            letter-spacing: -.01em;
        }
        .hero .tagline {
            color: rgba(255, 255, 255, .82);
            margin-top: .5rem; font-size: .93rem; line-height: 1.6;
        }

        /* ---------- Statistik ---------- */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .stat-box {
            position: relative;
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.15rem 1.3rem 1.15rem 1.45rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--line);
            overflow: hidden;
        }
        .stat-box::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #42a5f5, var(--blue-700));
        }
        .stat-label {
            font-size: .72rem; text-transform: uppercase; letter-spacing: .06em;
            color: var(--ink-soft); font-weight: 700;
        }
        .stat-value {
            font-size: 1.45rem; font-weight: 700; color: var(--blue-900);
            margin: .3rem 0 .2rem; line-height: 1.25;
            word-break: break-word;
        }
        .stat-hint { font-size: .78rem; color: #8a97a6; }

        /* ---------- Judul bagian ---------- */
        .section-title {
            display: flex; align-items: center; gap: .55rem;
            color: var(--blue-900); font-size: 1rem; font-weight: 700;
            margin: 0 0 .9rem;
        }
        .section-title::before {
            content: ''; width: 4px; height: 16px; border-radius: 2px;
            background: var(--blue-700);
        }

        /* ---------- Kartu menu ---------- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
            gap: 1rem;
            margin-bottom: 1.9rem;
        }
        .card {
            display: flex;
            gap: .9rem;
            align-items: flex-start;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.15rem 1.2rem;
            box-shadow: var(--shadow);
            text-decoration: none;
            color: inherit;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        a.card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: #bbdefb;
        }
        .card-icon {
            width: 42px; height: 42px; flex-shrink: 0;
            display: grid; place-items: center;
            border-radius: 11px;
            background: var(--blue-100);
        }
        .card-icon svg { width: 22px; height: 22px; stroke: var(--blue-700); }
        .card-body { min-width: 0; }
        .card-body h3 {
            color: var(--blue-900); font-size: .98rem; font-weight: 700;
            margin-bottom: .25rem; line-height: 1.35;
        }
        .card-body p { color: var(--ink-soft); font-size: .855rem; line-height: 1.5; }
        .card.card-static { cursor: default; opacity: .82; }
        .card.card-static .card-icon { background: #eceff1; }
        .card.card-static .card-icon svg { stroke: #90a4ae; }

        /* ---------- Tips ---------- */
        .tips {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        .tips ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: .65rem 1.4rem;
            margin-top: .25rem;
        }
        .tips li {
            display: flex; align-items: flex-start; gap: .75rem;
            font-size: .875rem; color: var(--ink-soft); line-height: 1.55;
            transition: transform .2s ease;
            cursor: default;
        }
        .tips li:hover {
            transform: translateX(4px);
        }
        .tips-icon {
            width: 24px; height: 24px; flex-shrink: 0; margin-top: .1rem;
            display: grid; place-items: center;
            border-radius: 50%;
            transition: transform .3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .tips li:hover .tips-icon {
            transform: scale(1.25) rotate(10deg);
        }
        .tips-icon svg {
            width: 15px; height: 15px;
            stroke-width: 2.2;
        }

        /* ---------- Responsif ---------- */
        @media (max-width: 860px) {
            .header { flex-direction: column; align-items: stretch; }
            .header-right { justify-content: space-between; }
        }
        @media (max-width: 560px) {
            .container { padding: 1rem .85rem 2.5rem; }
            .hero { padding: 1.5rem 1.35rem; }
            .hero .greeting { font-size: 1.28rem; }
            .grid { grid-template-columns: 1fr; }
            .user-chip { max-width: 100%; }
            .stat-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @php
        $role = auth()->user()->role;
        $roleLabel = [
            'patient' => 'Pasien',
            'doctor' => 'Dokter',
            'editor' => 'Editor',
            'admin' => 'Administrator',
        ][$role] ?? ucfirst($role);
        $initials = collect(explode(' ', trim(auth()->user()->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('');
    @endphp

    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="brand">
                <div class="brand-mark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12h4l2-5 3 10 2-5h7" />
                    </svg>
                </div>
                <div class="brand-text">
                    <h1>LambungCare</h1>
                    <span>Sistem Pakar Gejala Penyakit Lambung</span>
                </div>
            </div>

            <div class="header-right">
                <div class="user-chip">
                    <span class="avatar">{{ $initials }}</span>
                    <span class="name">{{ auth()->user()->name }} &middot; {{ $roleLabel }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">Keluar</button>
                </form>
            </div>
        </div>

        {{-- Hero sambutan --}}
        <div class="hero">
            <div class="hero-content">
                <span class="eyebrow">{{ $roleLabel }}</span>
                <div class="greeting">Selamat datang, {{ auth()->user()->name }}!</div>
                <div class="tagline">
                    @if(auth()->user()->hasRole('patient'))
                        Kenali gejala lambung Anda, lalu dapatkan gambaran awal sebelum ke dokter.
                    @elseif($role === 'doctor' || $role === 'admin')
                        Tinjau hasil diagnosa pasien dan berikan penilaian medis Anda.
                    @else
                        Kelola data penyakit, gejala, dan aturan penalaran sistem.
                    @endif
                </div>
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

        {{-- Aktivitas Terbaru (Khusus Admin) --}}
        @if(auth()->user()->hasRole('admin'))
            <p class="section-title">Aktivitas Terbaru</p>
            <div class="grid" style="margin-bottom: 1.9rem;">
                {{-- Pendaftar Baru --}}
                <div class="card" style="display: block; padding: 1.25rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--blue-900); border-bottom: 1px solid var(--line); padding-bottom: .5rem; font-size: 1rem;">Pendaftar Baru</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @forelse($recentUsers as $u)
                            <li style="display: flex; justify-content: space-between; align-items: center; padding: .6rem 0; border-bottom: 1px solid var(--line); font-size: .85rem;">
                                <div>
                                    <div style="font-weight: 600; color: var(--ink);">{{ $u->name }}</div>
                                    <div style="font-size: .75rem; color: var(--ink-soft);">{{ $u->email }}</div>
                                </div>
                                <span style="color: #8a97a6; font-size: .75rem;">{{ $u->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="muted" style="font-size: .85rem;">Belum ada pendaftar baru.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Konsultasi Baru --}}
                <div class="card" style="display: block; padding: 1.25rem;">
                    <h3 style="margin-bottom: 1rem; color: var(--blue-900); border-bottom: 1px solid var(--line); padding-bottom: .5rem; font-size: 1rem;">Konsultasi Terbaru</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @forelse($recentConsultations as $c)
                            <li style="display: flex; justify-content: space-between; align-items: center; padding: .6rem 0; border-bottom: 1px solid var(--line); font-size: .85rem;">
                                <div>
                                    <div style="font-weight: 600; color: var(--ink);">{{ $c->user->name ?? 'Pengguna Terhapus' }}</div>
                                    <div style="font-size: .75rem; color: var(--ink-soft);">
                                        @if($c->status === 'diagnosed' && $c->diagnoses->firstWhere('rank', 1))
                                            {{ Str::before($c->diagnoses->firstWhere('rank', 1)->disease->name, ' (') }}
                                        @else
                                            Belum disimpulkan
                                        @endif
                                    </div>
                                </div>
                                <span style="color: #8a97a6; font-size: .75rem;">{{ $c->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="muted" style="font-size: .85rem;">Belum ada konsultasi baru.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        {{-- Menu --}}
        <p class="section-title">Menu</p>
        <div class="grid">
            @can('update-profile')
            <a class="card" href="{{ route('profile.edit') }}">
                <span class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="3.5" />
                        <path d="M4.5 20a7.5 7.5 0 0 1 15 0" />
                    </svg>
                </span>
                <span class="card-body">
                    <h3>Profil Saya</h3>
                    <p>Lengkapi data diri untuk dicetak pada hasil diagnosa.</p>
                </span>
            </a>
            @endcan

            @can('schedule-appointment')
            <a class="card" href="{{ route('detection.appointments.index') }}">
                <span class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </span>
                <span class="card-body">
                    <h3>Janji Temu</h3>
                    <p>Jadwalkan konsultasi tatap muka dengan dokter di Puskesmas.</p>
                </span>
            </a>
            @endcan

            @if(auth()->user()->can('access-education'))
                <a class="card" href="{{ route('detection.diseases') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 6.5A3.5 3.5 0 0 0 5 7v5a7 7 0 0 0 14 0V7a3.5 3.5 0 0 0-7-.5Z" />
                            <path d="M12 6.5v10" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Mengenal Penyakit Lambung</h3>
                        <p>Pelajari jenis-jenis penyakit lambung sebelum berkonsultasi.</p>
                    </span>
                </a>

                <a class="card" href="{{ route('detection.consultation') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                            <path d="M9 3h6M9 11h6M9 15h4" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Konsultasi</h3>
                        <p>Masukkan gejala yang Anda alami untuk mendapatkan diagnosa.</p>
                    </span>
                </a>
            @endif

            @if(auth()->user()->can('manage-appointments'))
                <a class="card" href="{{ route('detection.appointments.index') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Kelola Janji Temu</h3>
                        <p>Konfirmasi dan kelola jadwal kedatangan pasien.</p>
                    </span>
                </a>
            @endif

            @if(auth()->user()->can('review-consultations'))
                <a class="card" href="{{ route('doctor.review.index') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19V5M4 19h16" />
                            <path d="M8 15v-4M12 15V7M16 15v-6" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Riwayat Diagnosa</h3>
                        <p>Tinjau &amp; verifikasi hasil diagnosa pasien.</p>
                    </span>
                </a>
            @endif

            @if(auth()->user()->can('manage-rules'))
                <a class="card" href="{{ route('editor.rules.index') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.3 4.3a2 2 0 0 1 3.4 0l.6 1a2 2 0 0 0 1.7 1h1.2a2 2 0 0 1 1.7 3l-.6 1a2 2 0 0 0 0 2l.6 1a2 2 0 0 1-1.7 3H16a2 2 0 0 0-1.7 1l-.6 1a2 2 0 0 1-3.4 0l-.6-1a2 2 0 0 0-1.7-1H6.8a2 2 0 0 1-1.7-3l.6-1a2 2 0 0 0 0-2l-.6-1a2 2 0 0 1 1.7-3H8a2 2 0 0 0 1.7-1Z" />
                            <circle cx="12" cy="12" r="2.6" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Kelola Aturan</h3>
                        <p>Atur bobot aturan penalaran antar gejala dan penyakit.</p>
                    </span>
                </a>

                <a class="card" href="{{ route('editor.symptoms.index') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 4v16M4 12h16" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Kelola Gejala</h3>
                        <p>Tambah, ubah, dan hapus daftar gejala beserta kategorinya.</p>
                    </span>
                </a>

                <a class="card" href="{{ route('editor.diseases.index') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5V5.5Z" />
                            <path d="M8 7h7M8 11h5" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Kelola Penyakit</h3>
                        <p>Tambah, edit, dan hapus data penyakit lambung.</p>
                    </span>
                </a>
            @endif

            @if(auth()->user()->can('manage-users'))
                <a class="card" href="{{ route('admin.users.index') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19" />
                            <circle cx="10" cy="8" r="3.2" />
                            <path d="M20 19v-1.5a3.5 3.5 0 0 0-2.6-3.4M15.5 5.2a3.2 3.2 0 0 1 0 5.6" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Kelola Pengguna</h3>
                        <p>Kelola role dan hak akses (permission) pengguna.</p>
                    </span>
                </a>
            @endif

            @if(auth()->user()->can('take-consultation'))
                <a class="card" href="{{ route('detection.history') }}">
                    <span class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                            <path d="M13 3v5h5M9 13h6M9 17h4" />
                        </svg>
                    </span>
                    <span class="card-body">
                        <h3>Hasil Diagnosa</h3>
                        <p>Lihat riwayat hasil diagnosa dan cetak laporannya.</p>
                    </span>
                </a>

            @endif
        </div>

        {{-- Tips kesehatan --}}
        <div class="tips">
            <p class="section-title">Tips Menjaga Kesehatan Lambung</p>
            @php
                $tips = [
                    [
                        'text' => 'Makan teratur dan jangan melewatkan waktu makan.',
                        'color' => '#e65100',
                        'bg' => '#fff3e0',
                        'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>' // Clock
                    ],
                    [
                        'text' => 'Kurangi makanan pedas, asam, dan berlemak berlebihan.',
                        'color' => '#d32f2f',
                        'bg' => '#ffebee',
                        'icon' => '<path d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"/>' // Fire
                    ],
                    [
                        'text' => 'Batasi kopi, minuman bersoda, dan alkohol.',
                        'color' => '#5d4037',
                        'bg' => '#efebe9',
                        'icon' => '<path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>' // Coffee
                    ],
                    [
                        'text' => 'Jangan langsung berbaring dalam 2-3 jam setelah makan.',
                        'color' => '#4527a0',
                        'bg' => '#ede7f6',
                        'icon' => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>' // Moon
                    ],
                    [
                        'text' => 'Berhenti merokok dan kelola stres dengan cukup istirahat.',
                        'color' => '#00695c',
                        'bg' => '#e0f2f1',
                        'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>' // Activity / Heartbeat
                    ],
                    [
                        'text' => 'Minum air putih yang cukup setiap hari.',
                        'color' => '#0277bd',
                        'bg' => '#e1f5fe',
                        'icon' => '<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>' // Water Drop
                    ],
                ];
            @endphp
            <ul>
                @foreach ($tips as $tip)
                    <li>
                        <span class="tips-icon" style="background-color: {{ $tip['bg'] }}; color: {{ $tip['color'] }};">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                {!! $tip['icon'] !!}
                            </svg>
                        </span>
                        <span>{{ $tip['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</body>
</html>
