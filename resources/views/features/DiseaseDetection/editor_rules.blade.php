@extends('layouts.app')

@section('title', 'Kelola Aturan')

@section('content')
    @include('features.DiseaseDetection._editor_tabs', ['active' => 'rules'])

    <div class="card">
        <h2>Matriks Bobot Aturan</h2>
        <p class="muted" style="margin-top: .35rem;">
            Isi bobot 0–5 untuk tiap pasangan gejala dan penyakit (0 atau kosong berarti tidak
            berkaitan). Bobot yang lebih tinggi membuat gejala itu lebih kuat mengarah ke penyakit
            tersebut. Perubahan langsung dipakai oleh mesin diagnosa setelah disimpan.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('editor.rules.update') }}">
        @csrf
        @method('PUT')

        <div class="card matrix-wrap">
            @if ($symptoms->isEmpty() || $diseases->isEmpty())
                <p class="muted">Tambahkan gejala dan penyakit terlebih dahulu sebelum mengisi aturan.</p>
            @else
                <table class="matrix">
                    <thead>
                        <tr>
                            <th class="sticky-col">Gejala \ Penyakit</th>
                            @foreach ($diseases as $disease)
                                <th>{{ $disease->code }}<br><span class="th-name">{{ $disease->name }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($symptoms as $symptom)
                            <tr>
                                <th class="sticky-col">
                                    <span class="code-badge">{{ $symptom->code }}</span>{{ $symptom->name }}
                                </th>
                                @foreach ($diseases as $disease)
                                    <td>
                                        <input type="number"
                                               name="weights[{{ $disease->id }}][{{ $symptom->id }}]"
                                               value="{{ $matrix[$disease->id][$symptom->id] ?? '' }}"
                                               min="0" max="5" inputmode="numeric" placeholder="0">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if ($symptoms->isNotEmpty() && $diseases->isNotEmpty())
            <div class="actions" style="margin-bottom: 2rem;">
                <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        @endif
    </form>
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
    .matrix-wrap { overflow-x: auto; }
    .matrix { border-collapse: collapse; width: 100%; min-width: 520px; }
    .matrix th, .matrix td { border: 1px solid #eceff1; padding: .5rem; text-align: center; font-size: .85rem; }
    .matrix thead th { background: #f5faff; color: #1565c0; font-weight: 700; vertical-align: bottom; }
    .matrix .th-name { font-weight: 400; font-size: .75rem; color: #607d8b; }
    .matrix tbody th { text-align: left; font-weight: 500; color: #37474f; white-space: nowrap; }
    .matrix .sticky-col { background: #fff; }
    .matrix input {
        width: 62px; padding: .4rem; border: 1px solid #ccc; border-radius: 6px;
        text-align: center; font-size: .9rem; font-family: inherit;
    }
@media(max-width: 768px) { .actions .btn { width: 100%; } }
@endsection
