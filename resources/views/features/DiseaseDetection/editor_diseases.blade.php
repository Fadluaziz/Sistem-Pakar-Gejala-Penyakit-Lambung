@extends('layouts.app')

@section('title', 'Kelola Penyakit')

@section('content')
    @include('features.DiseaseDetection._editor_tabs', ['active' => 'diseases'])

    <div class="card">
        <h2>Data Penyakit</h2>
        <p class="muted" style="margin-top: .35rem;">
            Ringkasan, deskripsi, pengobatan, dan pencegahan tampil pada halaman edukasi dan
            halaman hasil diagnosa pasien.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Tambah penyakit --}}
    <div class="card">
        <h3 style="margin-bottom: .75rem;">Tambah Penyakit</h3>
        <form method="POST" action="{{ route('editor.diseases.store') }}">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label for="new-code">Kode</label>
                    <input type="text" id="new-code" name="code" value="{{ old('code') }}" placeholder="P05" required>
                </div>
                <div class="form-group">
                    <label for="new-name">Nama Penyakit</label>
                    <input type="text" id="new-name" name="name" value="{{ old('name') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="new-summary">Ringkasan Singkat</label>
                <input type="text" id="new-summary" name="summary" value="{{ old('summary') }}" maxlength="500">
            </div>
            <div class="form-group">
                <label for="new-description">Deskripsi</label>
                <textarea id="new-description" name="description">{{ old('description') }}</textarea>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label for="new-treatment">Pengobatan</label>
                    <textarea id="new-treatment" name="treatment">{{ old('treatment') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="new-prevention">Pencegahan</label>
                    <textarea id="new-prevention" name="prevention">{{ old('prevention') }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Penyakit</button>
        </form>
    </div>

    {{-- Daftar penyakit --}}
    @forelse ($diseases as $disease)
        @php $used = in_array($disease->id, $usedIds, true); @endphp

        <div class="card">
            <form method="POST" action="{{ route('editor.diseases.update', $disease) }}">
                @csrf
                @method('PUT')

                <div class="grid-2">
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" name="code" value="{{ $disease->code }}" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Penyakit</label>
                        <input type="text" name="name" value="{{ $disease->name }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Ringkasan Singkat</label>
                    <input type="text" name="summary" value="{{ $disease->summary }}" maxlength="500">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description">{{ $disease->description }}</textarea>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Pengobatan</label>
                        <textarea name="treatment">{{ $disease->treatment }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Pencegahan</label>
                        <textarea name="prevention">{{ $disease->prevention }}</textarea>
                    </div>
                </div>

                <div class="actions" style="margin-top: 0;">
                    <span class="muted">
                        @if ($used)
                            Sudah punya riwayat diagnosa — tidak dapat dihapus.
                        @endif
                    </span>
                    <button type="submit" class="btn btn-secondary">Simpan Perubahan</button>
                </div>
            </form>

            <form method="POST" action="{{ route('editor.diseases.destroy', $disease) }}"
                  onsubmit="return confirm('Hapus penyakit ini?')" style="margin-top: .75rem;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" @disabled($used)
                        title="{{ $used ? 'Sudah punya riwayat diagnosa' : 'Hapus penyakit' }}">
                    Hapus Penyakit
                </button>
            </form>
        </div>
    @empty
        <div class="card"><p class="muted">Belum ada penyakit.</p></div>
    @endforelse

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
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
    .btn-sm { padding: .45rem .9rem; font-size: .85rem; }
    .btn-danger { background: #e53935; color: #fff; }
    .btn-danger:hover { background: #c62828; }
    .btn-danger:disabled { opacity: .45; cursor: not-allowed; }
    .btn-danger:disabled:hover { background: #e53935; }
    @media(max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
@endsection
