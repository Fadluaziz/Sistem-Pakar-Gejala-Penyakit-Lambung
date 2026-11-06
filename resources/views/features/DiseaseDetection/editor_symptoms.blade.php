@extends('layouts.app')

@section('title', 'Kelola Gejala')

@section('content')
    @include('features.DiseaseDetection._editor_tabs', ['active' => 'symptoms'])

    <div class="card">
        <h2>Data Gejala</h2>
        <p class="muted" style="margin-top: .35rem;">
            Kategori menentukan di bagian mana gejala ditanyakan saat konsultasi. Gejala
            berkategori "Tanda Bahaya" juga memicu peringatan darurat pada hasil.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Tambah gejala --}}
    <div class="card">
        <h3 style="margin-bottom: .75rem;">Tambah Gejala</h3>
        <form method="POST" action="{{ route('editor.symptoms.store') }}" class="entity-row">
            @csrf
            <input type="text" name="code" placeholder="Kode (G26)" value="{{ old('code') }}" class="f-code" required>
            <input type="text" name="name" placeholder="Nama gejala" value="{{ old('name') }}" class="f-name" required>
            <select name="category" class="f-cat" required>
                @foreach (\App\Features\DiseaseDetection\Models\Symptom::CATEGORIES as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>

    {{-- Daftar gejala --}}
    <div class="card">
        <h3 style="margin-bottom: .75rem;">Daftar Gejala ({{ $symptoms->count() }})</h3>

        @forelse ($symptoms as $symptom)
            @php $used = in_array($symptom->id, $usedIds, true); @endphp

            <div class="entity-row">
                <form method="POST" action="{{ route('editor.symptoms.update', $symptom) }}" class="entity-edit">
                    @csrf
                    @method('PUT')
                    <input type="text" name="code" value="{{ $symptom->code }}" class="f-code" required>
                    <input type="text" name="name" value="{{ $symptom->name }}" class="f-name" required>
                    <select name="category" class="f-cat" required>
                        @foreach (\App\Features\DiseaseDetection\Models\Symptom::CATEGORIES as $category)
                            <option value="{{ $category }}" @selected($symptom->category === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">Simpan</button>
                </form>

                <form method="POST" action="{{ route('editor.symptoms.destroy', $symptom) }}"
                      onsubmit="return confirm('Hapus gejala ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            @disabled($used)
                            title="{{ $used ? 'Sudah dipakai pada riwayat konsultasi' : 'Hapus gejala' }}">
                        Hapus
                    </button>
                </form>
            </div>
        @empty
            <p class="muted">Belum ada gejala.</p>
        @endforelse
    </div>

    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
        <span></span>
    </div>
@endsection

@section('styles')
    .container { max-width: 1100px; }
    .editor-tabs { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
    .etab {
        padding: .5rem 1.2rem; border-radius: 999px; text-decoration: none; font-size: .9rem;
        background: #eceff1; color: #37474f; font-weight: 600;
    }
    .etab.active { background: #1565c0; color: #fff; }
    .etab:hover { background: #cfd8dc; }
    .etab.active:hover { background: #0d47a1; }

    .entity-row {
        display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;
        padding: .5rem 0; border-bottom: 1px solid #f0f0f0;
    }
    .entity-edit { display: flex; align-items: center; gap: .5rem; flex: 1; flex-wrap: wrap; }
    .entity-row input, .entity-row select {
        padding: .5rem .6rem; border: 1px solid #ccc; border-radius: 6px;
        font-size: .9rem; font-family: inherit;
    }
    .f-code { width: 100px; }
    .f-name { flex: 1; min-width: 180px; }
    .f-cat { width: 150px; }
    .btn-sm { padding: .45rem .9rem; font-size: .85rem; }
    .btn-danger { background: #e53935; color: #fff; }
    .btn-danger:hover { background: #c62828; }
    .btn-danger:disabled { opacity: .45; cursor: not-allowed; }
    .btn-danger:disabled:hover { background: #e53935; }
@endsection
