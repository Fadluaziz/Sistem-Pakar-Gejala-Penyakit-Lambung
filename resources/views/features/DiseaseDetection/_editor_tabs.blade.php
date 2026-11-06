{{-- Navigasi tab panel editor; $active berisi slug tab yang sedang dibuka. --}}
@php $active = $active ?? ''; @endphp
<div class="editor-tabs">
    <a href="{{ route('editor.rules.index') }}" class="etab {{ $active === 'rules' ? 'active' : '' }}">Aturan</a>
    <a href="{{ route('editor.symptoms.index') }}" class="etab {{ $active === 'symptoms' ? 'active' : '' }}">Gejala</a>
    <a href="{{ route('editor.diseases.index') }}" class="etab {{ $active === 'diseases' ? 'active' : '' }}">Penyakit</a>
</div>
